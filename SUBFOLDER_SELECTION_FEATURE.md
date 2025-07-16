# FileBird Frontend Documents - Subfolder Selection Feature

## Overview

The plugin now includes a powerful subfolder selection feature that allows users to granularly control which folders are included in their shortcode output. This feature provides a visual interface for selecting/deselecting subfolders and automatically generates the appropriate `exclude_folders` attribute.

## New Features

### 1. Subfolder Selection Interface

When a folder with subfolders is selected, a new panel appears showing:
- **Parent folder** (always checked, cannot be unchecked)
- **All subfolders** with checkboxes
- **Document counts** for each folder
- **Check All/Uncheck All** buttons for bulk operations

### 2. New Shortcode Attribute: `exclude_folders`

The plugin now supports a new shortcode attribute:
```
[filebird_docs folder="123" exclude_folders="456,789,101112"]
```

**Parameters:**
- `exclude_folders`: Comma-separated list of folder IDs to exclude from the display

### 3. Automatic Shortcode Generation

The admin interface automatically:
- Detects when subfolders are unchecked
- Generates the `exclude_folders` attribute
- Updates the shortcode in real-time

## How It Works

### Frontend (Admin Interface)

1. **Folder Selection**: User clicks on a folder in the tree
2. **Subfolder Detection**: System automatically finds all subfolders
3. **Checkbox Population**: Creates checkboxes for each subfolder
4. **Real-time Updates**: As checkboxes are toggled, shortcode updates
5. **Bulk Operations**: Check All/Uncheck All buttons for convenience

### Backend Processing

1. **Attribute Parsing**: Shortcode handler parses `exclude_folders` attribute
2. **Folder Filtering**: Excluded folders are removed from processing
3. **Attachment Collection**: Only non-excluded folders are processed
4. **Output Generation**: Final display excludes specified folders

## User Experience

### Selecting a Folder
1. Click on any folder in the tree
2. If the folder has subfolders, the subfolder panel appears
3. All subfolders are checked by default (included)
4. Uncheck folders you want to exclude

### Bulk Operations
- **Check All**: Includes all subfolders
- **Uncheck All**: Excludes all subfolders (except parent)

### Shortcode Generation
- The shortcode automatically includes `exclude_folders` when needed
- Example: `[filebird_docs folder="123" exclude_folders="456,789"]`

## Technical Implementation

### JavaScript Changes

**New Methods:**
- `populateSubfolders()`: Creates checkbox interface
- `findSubfolders()`: Recursively finds all subfolders
- `updateExcludedSubfolders()`: Tracks unchecked folders
- `checkAllSubfolders()` / `uncheckAllSubfolders()`: Bulk operations

**Enhanced Methods:**
- `selectFolder()`: Now populates subfolders
- `updateShortcode()`: Includes exclude_folders attribute
- `clearSelection()`: Resets subfolder state

### PHP Changes

**Shortcode Handler:**
- Added `exclude_folders` attribute support
- Parses comma-separated folder IDs
- Passes excluded folders to helper methods

**FileBird Helper:**
- Updated `getAttachmentsByFolderIdRecursive()` to filter excluded folders
- Updated `collectFolderAttachments()` to skip excluded folders
- Added exclude_folders parameter to all relevant methods

### CSS Styling

**New Styles:**
- Subfolder controls container
- Checkbox styling and layout
- Bulk action buttons
- Responsive design for mobile

## Usage Examples

### Basic Usage
```
[filebird_docs folder="123"]
```
Displays all documents from folder 123 and its subfolders.

### Exclude Specific Subfolders
```
[filebird_docs folder="123" exclude_folders="456,789"]
```
Displays documents from folder 123 and its subfolders, except folders 456 and 789.

### Combined with Other Attributes
```
[filebird_docs folder="123" exclude_folders="456,789" layout="grid" columns="4" include_subfolders="true"]
```
Advanced usage with multiple attributes.

## Benefits

### 1. Granular Control
- Selectively include/exclude specific subfolders
- Maintain folder hierarchy while controlling content
- Perfect for large folder structures

### 2. User-Friendly Interface
- Visual checkbox interface
- Real-time shortcode updates
- Bulk operations for efficiency

### 3. Flexible Implementation
- Works with all existing shortcode attributes
- Compatible with grouped folder display
- Supports recursive subfolder exclusion

### 4. Performance Optimized
- Only processes non-excluded folders
- Efficient filtering at the query level
- Minimal impact on page load times

## Edge Cases Handled

### 1. No Subfolders
- Subfolder panel doesn't appear
- No exclude_folders attribute generated

### 2. All Subfolders Excluded
- Only parent folder content displayed
- Shortcode works normally

### 3. Invalid Folder IDs
- Graceful error handling
- Clear error messages

### 4. Deep Nesting
- Recursive subfolder detection
- Unlimited nesting levels supported

## Future Enhancements

### Potential Improvements
1. **Folder Preview**: Show folder contents on hover
2. **Search in Subfolders**: Filter subfolder list
3. **Folder Groups**: Group related subfolders
4. **Template Overrides**: Custom subfolder display templates
5. **Bulk Import/Export**: Save/load folder exclusion lists

### Advanced Features
1. **Conditional Exclusion**: Exclude based on folder properties
2. **Dynamic Exclusion**: Exclude based on user roles
3. **Exclusion Rules**: Create reusable exclusion patterns
4. **Analytics**: Track which folders are commonly excluded

## Compatibility

### WordPress Requirements
- WordPress 5.0+
- PHP 7.4+
- FileBird plugin (latest version)

### Browser Support
- Modern browsers with ES6 support
- Responsive design for mobile devices
- Progressive enhancement for older browsers

## Testing

### Manual Testing Checklist
1. [ ] Select folder with subfolders
2. [ ] Verify subfolder panel appears
3. [ ] Test individual checkbox toggles
4. [ ] Test Check All/Uncheck All buttons
5. [ ] Verify shortcode updates in real-time
6. [ ] Test with deep folder nesting
7. [ ] Test with no subfolders
8. [ ] Test mobile responsiveness
9. [ ] Verify excluded folders don't appear in output
10. [ ] Test with grouped folder display

### Automated Testing
- Unit tests for helper methods
- Integration tests for shortcode processing
- JavaScript tests for UI interactions
- CSS regression tests for styling

## Conclusion

The subfolder selection feature significantly enhances the plugin's usability by providing granular control over folder content while maintaining an intuitive user interface. This feature makes the plugin suitable for complex folder structures and provides users with the flexibility they need for precise content control. 