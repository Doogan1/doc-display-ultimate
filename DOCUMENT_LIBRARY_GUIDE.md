# Document Library Custom Post Type Guide

## Overview

The Document Library custom post type provides an easy way for content editors to create and manage document displays through the WordPress admin interface, without needing to write shortcodes manually.

## Features

### ✅ Easy Admin Interface
- Visual folder selector using the existing FileBird folder tree
- Form-based configuration of all shortcode attributes
- Live shortcode generation
- Preview capabilities

### ✅ Frontend Editor Access
- "Edit Library" button appears on frontend for administrators
- Direct link to library settings
- Seamless editing workflow

### ✅ Complete Shortcode Compatibility
- All existing shortcode attributes supported
- Automatic shortcode generation
- Backward compatibility maintained

## How to Use

### Creating a Document Library

1. **Navigate to Document Libraries**
   - Go to WordPress Admin → Document Libraries
   - Click "Add New"

2. **Set the Title**
   - Give your library a descriptive name
   - This will help identify it in the admin

3. **Configure Settings**
   - **FileBird Folders**: Select which folders to include
   - **Layout**: Choose Grid, List, or Table
   - **Columns**: Set number of columns for grid layout
   - **Order By**: Date, Title, Menu Order, or ID
   - **Order**: Ascending or Descending
   - **Limit**: Number of documents to show (-1 for all)
   - **Display Options**: Toggle title, size, date, thumbnail
   - **Subfolder Options**: Include subfolders, group by folder, accordion settings
   - **Exclude Folders**: Comma-separated folder IDs to exclude
   - **Custom CSS Class**: Additional styling classes

4. **Use the Shortcode**
   - Copy the generated shortcode from the sidebar
   - Paste it into any post or page

### Managing Document Libraries

- **Edit**: Click "Edit Library" button on frontend or edit from admin
- **Duplicate**: Create new library with similar settings
- **Delete**: Remove unused libraries
- **Bulk Actions**: Manage multiple libraries at once

## Shortcode Reference

### Basic Usage
```php
[render_document_library id="123"]
```

### Generated Shortcode Example
The plugin automatically generates a shortcode like this:
```php
[filebird_docs folder="1,2,3" layout="grid" columns="3" orderby="date" order="DESC" limit="-1" show_title="true" show_size="false" show_date="false" show_thumbnail="true" include_subfolders="false" group_by_folder="false" accordion_default="closed" class="custom-library"]
```

## Admin Interface Features

### Folder Selector
- Tree-based folder selection
- Multi-select capability
- Visual folder hierarchy
- Search functionality

### Settings Panel
- Organized into logical sections
- Real-time validation
- Default value suggestions
- Help text for each option

### Shortcode Sidebar
- Always-visible shortcode display
- One-click copy functionality
- Preview of generated shortcode
- Usage instructions

## Frontend Features

### Editor Button
- Only visible to administrators
- Styled to match WordPress admin
- Responsive design
- Accessible keyboard navigation

### Error Handling
- Invalid library ID detection
- Graceful error messages
- Fallback content when needed

## Technical Details

### Database Schema
- Custom post type: `document_library`
- Meta fields prefixed with `_document_library_`
- All settings stored as post meta

### Security
- Nonce verification for all forms
- Capability checks for editing
- Sanitized input/output
- XSS protection

### Performance
- Efficient meta queries
- Cached folder data
- Optimized shortcode processing
- Minimal database overhead

## Customization

### Styling the Editor Button
```css
.doc-library-edit-button {
    /* Custom styles */
    background: #your-color;
    border-radius: 5px;
}
```

### Adding Custom Fields
```php
// Add to your theme's functions.php
add_action('add_meta_boxes', 'add_custom_library_field');
function add_custom_library_field() {
    add_meta_box(
        'custom_library_field',
        'Custom Field',
        'render_custom_field',
        'document_library'
    );
}
```

### Filtering Library Output
```php
// Modify library settings before rendering
add_filter('document_library_settings', 'modify_library_settings');
function modify_library_settings($settings) {
    // Modify settings here
    return $settings;
}
```

## Troubleshooting

### Common Issues

**Library not appearing in admin menu**
- Check if FileBird plugin is active
- Verify plugin is properly activated
- Check for JavaScript errors in browser console

**Shortcode not rendering**
- Verify the library ID exists
- Check if folders contain documents
- Ensure FileBird is working correctly

**Editor button not showing**
- Verify user has edit permissions
- Check if user is logged in
- Ensure CSS is loading properly

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Migration from Manual Shortcodes

### Converting Existing Shortcodes
1. Create a new Document Library
2. Configure settings to match your shortcode
3. Replace shortcode with `[render_document_library id="X"]`
4. Test to ensure identical output

### Bulk Migration
For multiple shortcodes, consider creating a migration script:
```php
// Example migration function
function migrate_shortcodes_to_libraries() {
    // Find all [filebird_docs] shortcodes
    // Create corresponding libraries
    // Replace shortcodes with new format
}
```

## Future Enhancements

### Planned Features
- Bulk library creation
- Library templates
- Import/export functionality
- Advanced filtering options
- Custom field support
- REST API endpoints

### Contributing
- Report bugs via GitHub issues
- Submit feature requests
- Contribute code improvements
- Help with documentation

## Support

For technical support or feature requests, please refer to the main plugin documentation or contact the development team. 