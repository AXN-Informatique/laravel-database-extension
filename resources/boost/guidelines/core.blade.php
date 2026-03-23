# Laravel Database Extension

- Extends Laravel's Eloquent and Query Builder with macros, mixins, traits, and scopes for common database operations.
- Provides `joinRel()` / `leftJoinRel()` / `rightJoinRel()` macros to join tables using Eloquent relationship definitions (supports BelongsTo, HasOne, HasMany, MorphOne, MorphMany).
- Provides `whereHasIn()` as a performance alternative to `whereHas()` using IN clauses instead of EXISTS.
- Provides `whereLike()` macro for simplified search across multiple columns and relationships.
- Provides `orderByNatural()` / `orderByNaturalDesc()` macros for natural sorting (1, 2, 10 instead of 1, 10, 2). Only works on columns beginning with numerics.
- Provides `DefaultOrderScope` global scope for defining default model ordering. Kept for backward compatibility only.
- Provides an extended `SoftDeletes` trait with `withoutTrashedExcept($id)` for excluding specific records from soft-delete filtering.
- IMPORTANT: Activate `database-extension-development` skill for detailed usage patterns, supported relationships, and examples.
