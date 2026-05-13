---
name: data-test-pest-browser
description: >-
  Adds stable data-test (or data-testid) hooks on UI and targets them from
  Pest browser tests with the @ prefix. Use when writing or updating
  tests/Browser tests, Playwright flows via pest-plugin-browser, or when the
  user asks for reliable selectors instead of visible text/CSS.
disable-model-invocation: true
---

# data-test hooks for Pest browser tests

## Why

Visible copy, CSS classes, and DOM depth change often. **`data-test`** (or **`data-testid`**) gives tests a **stable contract** on the element you intend to click, fill, or assert against.

## Markup (React SPA)

Add a **`data-test`** string on the actionable element (button, link, row action, etc.). Prefer **kebab-case**, one purpose per value, reused only when semantics match.

```tsx
<Button type="submit" data-test="login">
  Sign in
</Button>
```

`data-testid` works the same if you prefer that name elsewhere; this project mostly uses **`data-test`**.

## Pest browser: `@` maps to data attributes

With **`pestphp/pest-plugin-browser`**, strings passed to `click`, `type`, `fill`, `assertSeeIn`, etc. go through **GuessLocator**. If the string **starts with `@`**, it targets **either** attribute:

- `[data-testid="<id>"]`
- `[data-test="<id>"]`

The **`@` is not part of the attribute value** — it is only the Pest shorthand.

Examples:

```php
visit('/login')
    ->type('#email', 'user@example.com')
    ->type('#password', 'secret')
    ->click('@login');
```

```php
$page->click('@create-account')
    ->waitForText('Sign out', 10);
```

Use the same `@slug` anywhere `guessLocator` applies: **`press`**, **`hover`**, **`check`**, assertions that take a selector, etc.

## Naming

- **Stable**: `"login"` not `"sign-in-may-2026"`.
- **Specific enough**: `"profile-logout"` not `"logout"` if multiple logouts exist.
- **Avoid dynamic values in the primary hook** when a single locator should be unique; if you need stateful hooks (e.g. connection status), keep values enumerable so tests can assert each case deliberately.

## When not to use `@`

- Selectors that already are explicit CSS (`#id`, `.class`, `[name="..."]`) are resolved first when they match **GuessLocator** rules — use those for generated fields if needed.
- Plain text without `@` is treated as **getByText** — fine for one-off smoke tests but brittle for primary flows.

## Quick checklist

1. Add **`data-test="your-hook"`** on the element the user (or test) interacts with.
2. In Pest: **`->click('@your-hook')`** (same string without relying on label text).
3. Run the focused browser test file after changing hooks or tests.
