<?php

namespace KTL\Hypernexus\Query;

use KTL\Hypernexus\Endpoint\Endpoint;
use KTL\Hypernexus\Http\BusinessCentralClient;

class QueryBuilder
{
    protected array $params = [];

    protected array $operators = [
        '=' => 'eq',
        '==' => 'eq',
        '!=' => 'ne',
        '<>' => 'ne',
        '>' => 'gt',
        '>=' => 'ge',
        '<' => 'lt',
        '<=' => 'le',
    ];

    public function __construct(
        protected BusinessCentralClient $client,
        protected Endpoint $endpoint,
    ) {
    }

    public function where(
        string|array $field,
        string $operator = '=',
        mixed $value = null,
        bool $raw = false,
        string $conjunction = 'and',
    ): static {
        if (is_array($field)) {
            foreach ($field as $column => $value) {
                $this->addWhere(
                    $column,
                    '=',
                    $value,
                    $raw,
                    $conjunction
                );
            }

            return $this;
        }

        return $this->addWhere(
            $field,
            $operator,
            $value,
            $raw,
            $conjunction
        );
    }

    public function orWhere(
        string $field,
        string $operator = '=',
        mixed $value = null,
        bool $raw = false,
    ): static {
        return $this->where(
            $field,
            $operator,
            $value,
            $raw,
            'or'
        );
    }

    public function whereIn(
        string $field,
        array $values,
        bool $raw = false,
    ): static {
        $expressions = [];

        foreach ($values as $value) {
            $expressions[] = sprintf(
                '%s eq %s',
                $field,
                $this->formatValue($value, $raw)
            );
        }

        return $this->appendFilter(
            '(' . implode(' or ', $expressions) . ')',
            'and'
        );
    }

    public function whereNotIn(
        string $field,
        array $values,
        bool $raw = false,
    ): static {
        foreach ($values as $value) {
            $this->addWhere(
                $field,
                '!=',
                $value,
                $raw,
                'and'
            );
        }

        return $this;
    }

    public function contains(
        string $field,
        mixed $value,
        bool $raw = false,
    ): static {
        $expression = sprintf(
            'contains(%s, %s)',
            $field,
            $this->formatValue($value, $raw)
        );

        return $this->appendFilter($expression, 'and');
    }

    public function with(string|array $relations): static
    {
        $relations = is_array($relations)
            ? $relations
            : [$relations];

        $existing = $this->params['$expand'] ?? null;

        $relations = array_filter($relations);

        $this->params['$expand'] = $existing
            ? $existing . ',' . implode(',', $relations)
            : implode(',', $relations);

        return $this;
    }

    public function select(array $fields): static
    {
        $this->params['$select'] = implode(',', $fields);

        return $this;
    }

    public function top(int $count): static
    {
        $this->params['$top'] = $count;

        return $this;
    }

    public function skip(int $count): static
    {
        $this->params['$skip'] = $count;

        return $this;
    }

    public function skipToken(string $token): static
    {
        $this->params['$skiptoken'] = $token;

        return $this;
    }

    public function orderBy(
        string $field,
        string $direction = 'asc',
    ): static {
        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException(
                'Order direction must be asc or desc.'
            );
        }

        $this->params['$orderby'] = "{$field} {$direction}";

        return $this;
    }

    public function count(): int
    {
        $response = $this
            ->query(['$count' => 'true'])
            ->get();

        return (int) ($response['@odata.count'] ?? 0);
    }

    public function first(array $fields = []): ?array
    {
        $this->top(1);

        if ($fields) {
            $this->select($fields);
        }

        $response = $this->get();

        return $response['value'][0] ?? null;
    }

    public function get(array $fields = []): array
    {
        if ($fields) {
            $this->select($fields);
        }

        return $this->client->request(
            'GET',
            $this->endpoint->path(),
            $this->params,
        );
    }

    public function create(array $data): array
    {
        return $this->client->request(
            'POST',
            $this->endpoint->path(),
            data: $data,
        );
    }

    public function update(
        string|array $keys,
        array $data,
    ): array {
        return $this->endpoint->update($keys, $data);
    }

    public function delete(string|array $keys): array
    {
        return $this->endpoint->delete($keys);
    }

    public function query(array $parameters): static
    {
        $this->params = array_merge(
            $this->params,
            $parameters
        );

        return $this;
    }

    public function paginate(
        ?int $perPage = null,
        ?string $cursor = null,
    ): Paginator {
        $perPage ??= config('hypernexus.api.per_page', 50);

        $params = $this->params;

        $params['$count'] = 'true';
        $params['$top'] = $perPage;

        if ($cursor !== null) {
            $params['$skiptoken'] = base64_decode($cursor);
        }

        $response = $this->endpoint->get($params);

        return new Paginator(
            items: $response['value'] ?? [],
            currentCursor: $cursor,
            nextCursor: $this->extractNextCursor(
                $response['@odata.nextLink'] ?? null
            ),
            total: isset($response['@odata.count'])
                ? (int) $response['@odata.count']
                : null,
            perPage: $perPage,
        );
    }

    protected function extractNextCursor(?string $nextLink): ?string
    {
        if (!$nextLink) {
            return null;
        }

        $query = parse_url($nextLink, PHP_URL_QUERY);

        if (!$query) {
            return null;
        }

        parse_str($query, $params);

        $skipToken = $params['$skiptoken'] ?? null;

        return $skipToken !== null
            ? base64_encode($skipToken)
            : null;
    }

    public function params(): array
    {
        return $this->params;
    }

    protected function addWhere(
        string $field,
        string $operator,
        mixed $value,
        bool $raw,
        string $conjunction,
    ): static {
        $operator = $this->operators[$operator] ?? $operator;

        $expression = sprintf(
            '%s %s %s',
            $field,
            $operator,
            $this->formatValue($value, $raw)
        );

        return $this->appendFilter(
            $expression,
            $conjunction
        );
    }

    protected function appendFilter(
        string $expression,
        string $conjunction,
    ): static {
        if (isset($this->params['$filter'])) {
            $this->params['$filter'] .=
                " {$conjunction} {$expression}";
        } else {
            $this->params['$filter'] = $expression;
        }

        return $this;
    }

    protected function formatValue(
        mixed $value,
        bool $raw = false,
    ): string {
        if ($raw) {
            return (string) $value;
        }

        return ODataValue::format($value);
    }
}