<?php

namespace KTL\Hypernexus\Query;

use IteratorAggregate;
use Traversable;

class Paginator implements IteratorAggregate
{
    public function __construct(
        protected array $items,
        protected ?string $currentCursor = null,
        protected ?string $nextCursor = null,
        protected ?int $total = null,
        protected int $perPage = 50,
    ) {
    }

    /**
     * Get the items returned by Business Central.
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the current cursor.
     */
    public function currentCursor(): ?string
    {
        return $this->currentCursor;
    }

    /**
     * Get the cursor for the next page.
     */
    public function nextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * Determine whether another page exists.
     */
    public function hasMorePages(): bool
    {
        return $this->nextCursor !== null;
    }

    /**
     * Get the total number of records, when available.
     */
    public function total(): ?int
    {
        return $this->total;
    }

    /**
     * Get the requested page size.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Number of items in the current page.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Convert paginator to an array.
     */
    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'currentCursor' => $this->currentCursor,
            'nextCursor' => $this->nextCursor,
            'total' => $this->total,
            'perPage' => $this->perPage,
            'hasMorePages' => $this->hasMorePages(),
        ];
    }

    /**
     * Allow foreach ($paginator as $item).
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Allow Laravel-ish serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}