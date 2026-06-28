# Bootstrap Quick Start Guide

## Quick Links
- Bootstrap Components: https://getbootstrap.com/docs/5.3/components/
- Bootstrap Utilities: https://getbootstrap.com/docs/5.3/utilities/
- Font Awesome Icons: https://fontawesome.com/icons/

---

## Getting Started

### 1. All Pages Automatically Include Bootstrap
Your header.php includes:
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/bootstrap-custom.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

### 2. Include Header in Your Page
```php
<?php require_once __DIR__ . '/../includes/header.php'; ?>
```

### 3. Include Footer in Your Page
```php
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

## Common Classes

### Layout
```html
<!-- Container -->
<div class="container">Full width container</div>
<div class="container-fluid">100% width</div>

<!-- Grid -->
<div class="row">
    <div class="col-md-6">50% on medium screens</div>
    <div class="col-md-6">50% on medium screens</div>
</div>

<!-- Spacing -->
<div class="m-3">Margin 3</div>
<div class="p-4">Padding 4</div>
<div class="mb-2">Margin-bottom 2</div>
<div class="pt-5">Padding-top 5</div>
```

### Typography
```html
<h1 class="display-1">Large heading</h1>
<p class="lead">Lead paragraph</p>
<small class="text-muted">Small muted text</small>
<strong class="fw-bold">Bold text</strong>
```

### Buttons
```html
<!-- Basic -->
<button class="btn btn-primary">Primary</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-danger">Danger</button>

<!-- Sizes -->
<button class="btn btn-sm btn-primary">Small</button>
<button class="btn btn-lg btn-primary">Large</button>

<!-- Outline -->
<button class="btn btn-outline-primary">Outline</button>

<!-- Disabled -->
<button class="btn btn-primary" disabled>Disabled</button>

<!-- With Icon -->
<button class="btn btn-primary">
    <i class="fas fa-plus"></i> Add Item
</button>

<!-- Block -->
<button class="btn btn-primary w-100">Full Width</button>
```

### Cards
```html
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">Card Title</h5>
    </div>
    <div class="card-body">
        <p class="card-text">Card content goes here</p>
    </div>
    <div class="card-footer bg-light">
        <button class="btn btn-sm btn-primary">Action</button>
    </div>
</div>
```

### Alerts
```html
<!-- Success -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> Success message
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Error -->
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> Error message
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Warning -->
<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle"></i> Warning message
</div>

<!-- Info -->
<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i> Info message
</div>
```

### Forms
```html
<form>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" required>
    </div>
    
    <div class="mb-3">
        <label for="country" class="form-label">Country</label>
        <select class="form-select" id="country">
            <option>Select...</option>
            <option>USA</option>
            <option>Nepal</option>
        </select>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="agree">
        <label class="form-check-label" for="agree">
            I agree to terms
        </label>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Tables
```html
<table class="table table-hover">
    <thead class="table-light">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>John Doe</td>
            <td>john@example.com</td>
            <td><button class="btn btn-sm btn-warning">Edit</button></td>
        </tr>
    </tbody>
</table>
```

### Badges
```html
<span class="badge bg-primary">Primary</span>
<span class="badge bg-success">Success</span>
<span class="badge bg-danger">Danger</span>
<span class="badge rounded-pill bg-warning text-dark">Pill</span>
```

### Modals
```html
<!-- Button to trigger -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal">
    Open Modal
</button>

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Modal content here
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
```

### Navbar
```html
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Brand</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

---

## Grid System

### Basic Grid
```html
<div class="row">
    <div class="col-md-6">Half width</div>
    <div class="col-md-6">Half width</div>
</div>

<div class="row">
    <div class="col-md-4">One third</div>
    <div class="col-md-4">One third</div>
    <div class="col-md-4">One third</div>
</div>
```

### Responsive Grid
```html
<div class="row">
    <!-- Full on mobile, half on tablet, third on desktop -->
    <div class="col-12 col-md-6 col-lg-4">Content</div>
    <div class="col-12 col-md-6 col-lg-4">Content</div>
    <div class="col-12 col-md-6 col-lg-4">Content</div>
</div>
```

### Breakpoints
- `col-*` → Extra small (< 576px)
- `col-sm-*` → Small (≥ 576px)
- `col-md-*` → Medium (≥ 768px)
- `col-lg-*` → Large (≥ 992px)
- `col-xl-*` → Extra large (≥ 1200px)
- `col-xxl-*` → XXL (≥ 1400px)

---

## Utility Classes

### Display
```html
<div class="d-none">Hidden</div>
<div class="d-block">Block</div>
<div class="d-flex">Flex</div>
<div class="d-grid">Grid</div>
```

### Flexbox
```html
<div class="d-flex">
    <div>Item</div>
    <div>Item</div>
</div>

<div class="d-flex justify-content-between">Spaced</div>
<div class="d-flex align-items-center">Centered vertically</div>
<div class="d-flex flex-column">Column direction</div>
<div class="d-flex gap-3">With gap</div>
```

### Text
```html
<p class="text-center">Centered</p>
<p class="text-primary">Blue text</p>
<p class="text-muted">Muted text</p>
<p class="fw-bold">Bold</p>
<p class="fs-5">Smaller text</p>
```

### Colors
```html
<div class="bg-primary">Primary background</div>
<div class="bg-success">Success background</div>
<div class="text-danger">Danger text</div>
```

### Spacing
```html
<!-- Margin: m-{number} -->
<div class="m-3">Margin all sides</div>
<div class="mt-2">Margin top</div>
<div class="mb-4">Margin bottom</div>

<!-- Padding: p-{number} -->
<div class="p-3">Padding all sides</div>
<div class="pt-2">Padding top</div>

<!-- Gap: gap-{number} -->
<div class="d-flex gap-3">Flex with gap</div>
```

---

## Colors

### Text Colors
```html
<p class="text-primary">Primary text</p>
<p class="text-success">Success text</p>
<p class="text-danger">Danger text</p>
<p class="text-warning">Warning text</p>
<p class="text-info">Info text</p>
<p class="text-muted">Muted text</p>
<p class="text-dark">Dark text</p>
```

### Background Colors
```html
<div class="bg-primary text-white">Primary</div>
<div class="bg-success text-white">Success</div>
<div class="bg-danger text-white">Danger</div>
<div class="bg-light">Light</div>
```

---

## Icons (Font Awesome)

```html
<!-- Basic -->
<i class="fas fa-home"></i>
<i class="fas fa-user"></i>
<i class="fas fa-shopping-cart"></i>

<!-- Sizes -->
<i class="fas fa-home fs-1"></i>
<i class="fas fa-home fs-5"></i>

<!-- Colors -->
<i class="fas fa-home text-primary"></i>
<i class="fas fa-heart text-danger"></i>

<!-- In Buttons -->
<button class="btn btn-primary">
    <i class="fas fa-plus"></i> Add
</button>

<!-- Spin Animation -->
<i class="fas fa-spinner fa-spin"></i>
```

---

## Common Patterns

### Loading Spinner
```html
<div class="text-center">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
```

### Pagination
```html
<nav aria-label="Page navigation">
    <ul class="pagination">
        <li class="page-item"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
    </ul>
</nav>
```

### Breadcrumb
```html
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Products</a></li>
        <li class="breadcrumb-item active">Item</li>
    </ol>
</nav>
```

---

## Tips & Tricks

1. **Always use container for content** - Better spacing and alignment
2. **Use utility classes** - Faster than writing custom CSS
3. **Mobile-first** - Design for mobile, then add larger breakpoints
4. **Consistent spacing** - Use margin/padding utilities
5. **Test responsiveness** - Check all breakpoints
6. **Keep it simple** - Use Bootstrap first, custom CSS second
7. **Color consistency** - Use predefined colors
8. **Accessibility** - Use semantic HTML and ARIA labels

---

## Resources

- **Bootstrap Official:** https://getbootstrap.com/
- **Bootstrap Examples:** https://getbootstrap.com/docs/5.3/examples/
- **Font Awesome:** https://fontawesome.com/
- **Design System:** Color palette and component library defined in `bootstrap-custom.css`

---

## Need Help?

Check the documentation files:
- `BOOTSTRAP_ENHANCEMENT.md` - Detailed enhancement overview
- `BOOTSTRAP_SUMMARY.md` - Complete summary of changes

Good luck with your project! 🚀
