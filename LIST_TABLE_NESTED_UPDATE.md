# List and Table Templates - Nested Folder Structure Update

## Issue Description
The list and table view templates were not properly handling the new nested folder structure. When using `include_subfolders="true"` and `group_by_folder="true"`, the list and table views were not displaying any documents because they were using the old template structure that expected a simple array of folder groups rather than the recursive nested structure.

## Root Cause
Both `document-grouped-list.php` and `document-grouped-table.php` templates were using a simple `foreach` loop to iterate through folder groups, but the new nested structure requires a recursive function to properly handle nested folders at multiple levels.

## Fixes Applied

### 1. Updated List Template (`document-grouped-list.php`)
- **Added recursive function**: `renderFolderGroups()` function to handle nested folder structure
- **Added level tracking**: `data-level` attribute to track nesting depth
- **Added proper accordion states**: Consistent with grid template accordion behavior
- **Added nested folder support**: Recursive rendering of child folders

### 2. Updated Table Template (`document-grouped-table.php`)
- **Added recursive function**: `renderFolderGroups()` function to handle nested folder structure
- **Added level tracking**: `data-level` attribute to track nesting depth
- **Added proper accordion states**: Consistent with grid template accordion behavior
- **Added nested folder support**: Recursive rendering of child folders

### 3. Enhanced CSS (`frontend.css`)
- **Added nested table styling**: Proper indentation and spacing for nested tables
- **Added nested list styling**: Proper indentation and spacing for nested lists
- **Consistent nested folder styling**: Unified styling across all layout types

## Key Changes Made

### Template Structure Changes
```php
// OLD: Simple foreach loop
foreach ($attachments as $folder_group) {
    // Render single level
}

// NEW: Recursive function
function renderFolderGroups($folder_groups, $atts, $level = 0) {
    foreach ($folder_groups as $folder_group) {
        // Render current level
        if (!empty($folder_group['children'])) {
            renderFolderGroups($folder_group['children'], $atts, $level + 1);
        }
    }
}
```

### CSS Additions
```css
/* Nested table styling */
.filebird-docs-nested-folders .filebird-docs-table-wrapper {
    margin-left: 1rem;
}

/* Nested list styling */
.filebird-docs-nested-folders .filebird-docs-list {
    margin-left: 1rem;
}
```

## Expected Behavior After Fix
1. **List View**: Nested folders display with proper indentation and accordion functionality
2. **Table View**: Nested folders display with proper indentation and accordion functionality
3. **Consistent Behavior**: All three layout types (grid, list, table) now handle nested folders identically
4. **Accordion States**: Nested accordions maintain proper open/closed states based on `accordion_default` setting
5. **Visual Hierarchy**: Clear visual indication of folder nesting levels

## Testing Scenarios
- [ ] Test list view with `include_subfolders="true"` and `group_by_folder="true"`
- [ ] Test table view with `include_subfolders="true"` and `group_by_folder="true"`
- [ ] Test nested folder display in both layouts
- [ ] Test accordion functionality in both layouts
- [ ] Test with `accordion_default="closed"` and `accordion_default="open"`
- [ ] Test deep nesting (3+ levels) in both layouts
- [ ] Test mixed content (folders with documents and subfolders)

## Files Modified
- `templates/document-grouped-list.php` - Added recursive folder rendering
- `templates/document-grouped-table.php` - Added recursive folder rendering
- `assets/css/frontend.css` - Added nested table and list styling

## Compatibility
- All existing shortcode attributes remain unchanged
- Backward compatibility maintained for non-nested folder structures
- Consistent behavior across all three layout types (grid, list, table) 