# Document Editing Feature

## Overview

The FileBird Frontend Documents plugin now includes a powerful document editing feature that allows users with appropriate permissions to edit documents directly from the frontend. This feature provides two main capabilities:

1. **Rename Documents**: Change the title/name of documents
2. **Replace Documents**: Upload new files to replace existing documents

## Features

### Permission-Based Access
- Only users with `edit_posts` capability can see and use the edit buttons
- Edit buttons are automatically hidden for non-authorized users
- Secure AJAX endpoints with nonce verification

### Edit Button Integration
- Edit buttons appear next to download buttons in all layouts:
  - Grid layout
  - List layout  
  - Table layout
  - Grouped layouts (with accordion functionality)

### Modal Interface
- Clean, modern modal design with tabbed interface
- Two tabs: "Rename" and "Replace File"
- Drag-and-drop file upload support
- File validation (type and size)
- Responsive design for mobile devices

### File Support
Supported file types for replacement:
- PDF documents
- Microsoft Office documents (Word, Excel, PowerPoint)
- Text files
- Image files (JPEG, PNG, GIF, WebP)

### Security Features
- Nonce verification for all AJAX requests
- File type validation
- File size limits (10MB maximum)
- User permission checks
- Secure file handling

## Usage

### For End Users

1. **Viewing Documents**: Documents are displayed normally with download buttons
2. **Editing Documents**: If you have edit permissions, you'll see blue "Edit" buttons next to download buttons
3. **Opening the Editor**: Click any "Edit" button to open the editing modal
4. **Rename Tab**: Simply change the document title and click "Save Changes"
5. **Replace Tab**: 
   - Drag and drop a new file or click to browse
   - Update the title if needed
   - Click "Save Changes"
6. **File Validation**: The system will validate file type and size automatically

### For Developers

#### Adding Edit Buttons Programmatically

```php
// Check if user can edit documents
if (FileBird_FD_Document_Display::canEditDocuments()) {
    // Get edit button HTML
    $edit_button = FileBird_FD_Document_Display::getEditButton($attachment_id, $document_title);
    echo $edit_button;
}
```

#### Customizing Edit Button Appearance

The edit buttons use CSS classes that can be customized:

```css
.filebird-docs-edit-btn {
    /* Custom styling */
}

.filebird-docs-edit-btn:hover {
    /* Hover effects */
}
```

#### AJAX Endpoints

The plugin provides two AJAX endpoints:

1. **Rename Document**: `filebird_fd_rename_document`
   - Parameters: `attachment_id`, `document_title`, `filebird_fd_nonce`

2. **Replace Document**: `filebird_fd_replace_document`
   - Parameters: `attachment_id`, `document_title`, `document_file`, `filebird_fd_nonce`

## Technical Implementation

### Files Added/Modified

#### New Files
- `assets/js/document-editor.js` - JavaScript for modal functionality
- `assets/css/document-editor.css` - Styles for modal and edit buttons
- `DOCUMENT_EDITING_FEATURE.md` - This documentation

#### Modified Files
- `includes/class-document-display.php` - Added AJAX handlers and edit button methods
- `filebird-frontend-documents.php` - Updated to enqueue new assets
- All template files - Added edit buttons to document displays

### AJAX Handlers

The plugin includes two new AJAX handlers in the `FileBird_FD_Document_Display` class:

1. `ajaxReplaceDocument()` - Handles file replacement
2. `ajaxRenameDocument()` - Handles document renaming

### Security Considerations

- All AJAX requests require valid nonces
- User permissions are checked before any operations
- File uploads are validated for type and size
- Old files are properly deleted when replaced
- Attachment metadata is updated correctly

## Browser Support

The document editing feature works in all modern browsers:
- Chrome (recommended)
- Firefox
- Safari
- Edge

## Mobile Support

The modal interface is fully responsive and works on mobile devices:
- Touch-friendly interface
- Responsive design
- Optimized for small screens

## Troubleshooting

### Edit Buttons Not Visible
- Check user permissions (`edit_posts` capability)
- Ensure JavaScript is loading properly
- Check browser console for errors

### File Upload Issues
- Verify file type is supported
- Check file size (must be under 10MB)
- Ensure proper file permissions on server

### Modal Not Opening
- Check for JavaScript conflicts
- Verify jQuery is loaded
- Check browser console for errors

## Future Enhancements

Potential improvements for future versions:
- Bulk editing capabilities
- Version history tracking
- Advanced file type support
- Custom validation rules
- Integration with other plugins

## Support

For technical support or feature requests, please refer to the main plugin documentation or contact the development team. 