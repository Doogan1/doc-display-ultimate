# Multi-Folder Document Reordering Feature

## Overview

The Multi-Folder Document Reordering feature extends the FileBird Frontend Documents plugin to support drag-and-drop reordering of documents across multiple folders simultaneously. This enhancement allows users to manage document order across complex folder hierarchies while respecting subfolder inclusion/exclusion settings.

## Key Features

### 1. Multi-Folder Document Loading
- **Parent Folder Selection**: Users can select a parent folder and include all its subfolders
- **Subfolder Inclusion**: Optional inclusion of documents from subfolders recursively
- **Subfolder Exclusion**: Ability to exclude specific subfolders and their nested subfolders
- **Folder Grouping**: Documents are visually grouped by their source folder with clear headers

### 2. Individual Folder Sortables
- **Independent Sortables**: Each folder group has its own sortable instance
- **Document Isolation**: Documents can only be reordered within their own folder group
- **Drag Handle Restriction**: Only drag handles (⋮⋮) can be used to initiate dragging
- **Visual Feedback**: Clear drag states and placeholder indicators

### 3. Enhanced User Interface
- **Folder Summary**: Displays total documents, folders, and breakdown by folder
- **Folder Headers**: Each folder group shows folder name and document count
- **Document Indicators**: Each document shows its source folder name
- **Fresh Ordering**: Each folder group starts with document numbers 1, 2, 3...

## Technical Implementation

### Backend Changes

#### 1. Document Loading Logic (`class-document-order-manager.php`)

**Enhanced `ajaxGetDocumentsForOrdering()` method:**
- Accepts multiple `folder_ids` (comma-separated)
- Processes `include_subfolders` boolean flag
- Handles `exclude_folders` array with recursive exclusion
- Tracks source folder information for each document

**Key Algorithm:**
```php
// 1. Get all subfolder IDs recursively
$all_subfolder_ids = FileBird_FD_Helper::getSubfolderIds($folder_id);

// 2. Filter out excluded subfolders and their children
$excluded_subfolders = array();
foreach ($exclude_folders as $excluded_folder_id) {
    $excluded_subfolders = array_merge($excluded_subfolders, 
        FileBird_FD_Helper::getSubfolderIds($excluded_folder_id));
}
$all_excluded_folders = array_merge($exclude_folders, $excluded_subfolders);
$included_subfolder_ids = array_diff($all_subfolder_ids, $all_excluded_folders);

// 3. Process each folder individually
foreach ($folders_to_process as $current_folder_id) {
    $documents = FileBird_FD_Helper::getAttachmentsByFolderId($current_folder_id, $args);
    foreach ($documents as $doc) {
        $doc->source_folder_id = $current_folder_id;
        $doc->source_folder_name = $current_folder_name;
        $all_documents[] = $doc;
    }
}
```

#### 2. Order Update Logic

**Enhanced `updateDocumentOrderMultiFolder()` method:**
- Handles updating `menu_order` for documents across multiple folders
- Uses database transactions for data integrity
- Clears post cache for each affected folder

### Frontend Changes

#### 1. JavaScript Architecture (`order-manager.js`)

**Individual Folder Sortables:**
```javascript
// Initialize sortables for each folder group's documents
$('.filebird-fd-folder-documents').each(function(index) {
    var $folderDocuments = $(this);
    
    $folderDocuments.sortable({
        handle: '.filebird-fd-document-drag-handle',
        items: '.filebird-fd-document-item',
        placeholder: 'filebird-fd-document-item ui-sortable-placeholder',
        cancel: '.filebird-fd-folder-header'
    });
});
```

**Document Rendering:**
- Groups documents by `source_folder_name`
- Creates folder headers with document counts
- Assigns fresh ordering (1, 2, 3...) to each folder group
- Adds visual folder indicators to each document

#### 2. CSS Enhancements (`order-manager.css`)

**Folder Group Styling:**
- Distinct folder headers with background colors
- Clear visual separation between folder groups
- Non-draggable folder headers with proper cursor states
- Enhanced drag handle styling with hover effects

### 3. Document Library CPT Integration

**Enhanced Settings:**
- `include_subfolders` checkbox with proper ID attribute
- `exclude_folders` field for comma-separated folder IDs
- Integration with existing folder selection interface

## User Experience

### 1. Setup Process
1. **Select Parent Folder**: Choose the main folder containing subfolders
2. **Enable Subfolders**: Check "Include subfolders" to include nested documents
3. **Exclude Folders**: Optionally specify folders to exclude (comma-separated IDs)
4. **Load Documents**: System retrieves documents from all included folders

### 2. Reordering Interface
1. **Folder Groups**: Documents are grouped by their source folder
2. **Drag Handles**: Use the ⋮⋮ icons to drag documents
3. **Visual Feedback**: Clear drag states and placeholder indicators
4. **Order Numbers**: Each folder shows fresh 1, 2, 3... ordering

### 3. Save Process
1. **Dirty State**: Interface shows when changes are made
2. **Save Button**: Updates document order across all folders
3. **Database Transaction**: Ensures data integrity during updates
4. **Cache Clearing**: Refreshes affected folder caches

## Technical Challenges Solved

### 1. Sortable Instance Conflicts
**Problem**: Single sortable on container caused folder-level dragging
**Solution**: Individual sortables for each `.filebird-fd-folder-documents` container

### 2. Subfolder Exclusion Logic
**Problem**: Simple exclusion didn't handle nested subfolders
**Solution**: Recursive subfolder detection and exclusion

### 3. Document Source Tracking
**Problem**: Documents lost their source folder information
**Solution**: Explicit tracking of `source_folder_id` and `source_folder_name`

### 4. Event Propagation
**Problem**: Drag events conflicted with text selection
**Solution**: Proper event handling and CSS `user-select: none`

## File Changes Summary

### Modified Files
1. **`includes/class-document-order-manager.php`**
   - Enhanced `ajaxGetDocumentsForOrdering()`
   - Added `updateDocumentOrderMultiFolder()`
   - Improved subfolder exclusion logic

2. **`includes/class-document-library-cpt.php`**
   - Added missing `id` attribute to include_subfolders checkbox
   - Integrated multi-folder functionality

3. **`assets/js/order-manager.js`**
   - Implemented individual folder sortables
   - Enhanced document rendering with folder grouping
   - Added folder summary display
   - Fixed event handling conflicts

4. **`assets/css/order-manager.css`**
   - Added folder group styling
   - Enhanced drag handle appearance
   - Improved visual hierarchy

### New Features
- Multi-folder document loading
- Recursive subfolder exclusion
- Individual folder sortables
- Folder grouping and headers
- Enhanced user interface

## Browser Compatibility
- **jQuery UI Sortable**: Full compatibility with modern browsers
- **CSS Grid/Flexbox**: Responsive design across devices
- **Event Handling**: Cross-browser drag and drop support

## Performance Considerations
- **Database Transactions**: Ensures data integrity during bulk updates
- **Cache Management**: Clears affected folder caches
- **Lazy Loading**: Documents loaded on demand
- **Memory Management**: Proper cleanup of sortable instances

## Future Enhancements
- **Cross-folder dragging**: Allow moving documents between folders
- **Bulk operations**: Select and reorder multiple documents
- **Undo/Redo**: Track and reverse reordering operations
- **Export/Import**: Save and restore document order configurations

## Testing Scenarios
1. **Single folder**: Basic document reordering
2. **Multiple folders**: Cross-folder document management
3. **Subfolder inclusion**: Recursive document loading
4. **Subfolder exclusion**: Filtered document display
5. **Large datasets**: Performance with many documents
6. **Edge cases**: Empty folders, invalid IDs, network errors

This feature significantly enhances the document management capabilities of the FileBird Frontend Documents plugin, providing users with powerful tools for organizing documents across complex folder hierarchies while maintaining a clean and intuitive user interface. 