# Blaze Reference

Blaze compiles anonymous Blade components into optimized PHP functions, dramatically reducing render time for component-heavy pages.

## Current Setup

`Blaze::debug()` is the only active call — no optimization is applied yet.

## Three Optimization Strategies

### 1. Function Compiler (default — use this everywhere)

Converts templates to PHP functions. Safe for all dynamic components.

```blade
@blaze
@props([...])
```

Or apply to an entire directory (preferred):

```php
// AppServiceProvider::boot()
Blaze::optimize()->in(resource_path('views/components'));
```

### 2. Memoization (repeated renders with identical props)

Caches output per unique prop combination. No slots allowed.

```blade
@blaze memoize
@props(['name', 'value'])
```

Use when a component renders 10+ times per page with the same props — e.g. stat badges, player cards, table rows.

### 3. Compile-Time Folding (fully static content only)

Pre-renders to static HTML at compile time. Zero runtime cost. Dangerous if used on dynamic content.

```blade
@blaze fold
```

Only use for components with no dynamic data whatsoever.

## This Project

### Apply now

```php
// AppServiceProvider::boot()
if (app()->isLocal()) {
    Blaze::debug(); // dev overlay only
}
```

> **Note:** `Blaze::optimize()->in()` conflicts with Flux's own Blaze instrumentation on vendor components when they are nested inside compiled app components. Do not apply directory-wide optimization until you have leaf-level components that do not render any Flux sub-components. Apply `@blaze` per-component instead.

### Per-component guide

| Component | Strategy | Reason |
|---|---|---|
| `app-logo-icon.blade.php` | Skip — no `@props` | Uses `$attributes` without `@props`; Blaze does not inject the attributes bag |
| `app-logo.blade.php` | Skip — uses slots | Contains `<x-slot>` and nested Flux components |
| `desktop-user-menu.blade.php` | Skip — nested Flux | Contains `<flux:dropdown>` which has its own Blaze instrumentation |
| `placeholder-pattern.blade.php` | `@blaze` safe when ready | No nested components; function compiler is safe here |
| `⚡*.blade.php` (Livewire SFCs) | Not applicable | Blaze does not apply to Livewire components |

### Add memoization later for

- Player/team cards rendered in lists
- Stat or badge components rendered per-row in tables
- Any component rendered 10+ times per request with identical props

## Limitations

- No class-based components
- No `$component` variable
- No `View::share()` auto-injected variables
- No `@aware` directives across Blade/Blaze boundaries
- Cannot render Blaze components via the `view()` helper
- Memoization: no slot support

## Debugging

`Blaze::debug()` adds a dev overlay with render times and a flame chart profiler. Keep this local-only:

```php
if (app()->isLocal()) {
    Blaze::debug();
}
```
