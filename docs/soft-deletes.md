---
title: Extended SoftDeletes
order: 7
---

Extended SoftDeletes
====================

This package provides an extended `SoftDeletes` trait that adds the `withoutTrashedExcept` scope.

Setup
-----

Replace Laravel's `SoftDeletes` trait with the one from this package:

```php
namespace App\Models;

use Axn\Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use SoftDeletes;

    // ...
}
```

The trait extends Laravel's `SoftDeletes`, so all standard soft delete functionality remains available.

withoutTrashedExcept scope
--------------------------

This scope excludes trashed records except for specific IDs. This is useful when you want to show only active records but need to include a specific trashed record (e.g., the currently selected value in a dropdown).

### Single ID

```php
// Get all active post types, plus the one assigned to the current post
// (even if that post type has been soft-deleted)
$postTypes = PostType::withoutTrashedExcept($post->post_type_id)->get();
```

### Multiple IDs

```php
// Get all active post types, plus specific ones by ID
$postTypes = PostType::withoutTrashedExcept([1, 2, 3])->get();
```

### Null safe

If you pass `null`, it behaves like `withoutTrashed()`:

```php
// Equivalent to PostType::withoutTrashed()->get()
$postTypes = PostType::withoutTrashedExcept(null)->get();
```

Use case example
----------------

A common use case is populating a select dropdown while preserving the currently selected value:

```php
// In a controller
public function edit(Post $post)
{
    // Include the post's current type even if it's been soft-deleted
    $postTypes = PostType::withoutTrashedExcept($post->post_type_id)
        ->orderBy('name')
        ->get();

    return view('posts.edit', compact('post', 'postTypes'));
}
```

This ensures the user can still see and keep the current selection, while not showing other deleted options.
