# FileBird Frontend Documents Plugin - Complete Project Documentation

## Project Overview

**Plugin Name**: FileBird Frontend Documents  
**Version**: 0.1.0  
**Type**: WordPress Plugin  
**Dependencies**: FileBird plugin (required)  
**Purpose**: Extends FileBird media management to display organized documents on WordPress frontend using shortcodes

## Core Functionality

### Primary Features
- **Shortcode-based document display**: `[filebird_docs folder="123"]`
- **Multiple layout options**: Grid, List, and Table layouts
- **Hierarchical folder support**: Display nested folder structures with accordion functionality
- **Subfolder integration**: Include documents from subfolders recursively
- **Folder grouping**: Organize documents by folder structure with section headings
- **Responsive design**: Works on all devices including mobile
- **Accessibility**: Full keyboard navigation and screen reader support
- **Clean interface**: No document counts to avoid confusion with subfolder totals

### Advanced Features
- **Admin folder selector**: Tree-based folder selector in admin interface
- **Subfolder exclusion**: Exclude specific subfolders from display
- **Accordion folders**: Collapsible folder sections with smooth animations
- **Granular accordion control**: Select which folders should be open or closed by default
- **File type icons**: Visual indicators for different file types
- **Download tracking**: Enhanced download buttons with visual feedback
- **AJAX support**: Dynamic loading capabilities

### Document Library Custom Post Type
- **Centralized management**: Create and manage document displays through WordPress admin
- **Frontend editor buttons**: Administrators can edit libraries directly from frontend
- **Integrated settings**: All shortcode attributes configurable through admin interface
- **Usage tracking**: See where each library is used across the site
- **Document order manager**: Drag-and-drop reordering when using "Menu Order"

## Technical Architecture

### File Structure
```
doc-display-ultimate/
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin interface styling
│   │   ├── editor.css         # Frontend editor button styling
│   │   ├── frontend.css       # Frontend styling
│   │   └── order-manager.css  # Document order manager styling
│   └── js/
│       ├── admin.js           # Admin interface functionality
│       ├── frontend.js        # Frontend accordion and interactions
│       ├── order-manager.js   # Document order manager functionality
│       └── document-editor.js # Document editing modal functionality
├── includes/
│   ├── class-admin.php        # Admin interface class
│   ├── class-document-display.php  # Main plugin class
│   ├── class-document-library-cpt.php  # Document Library CPT
│   ├── class-document-order-manager.php # Document order manager
│   ├── class-filebird-helper.php   # FileBird integration
│   ├── class-logger.php       # Logging functionality
│   └── class-shortcode-handler.php # Shortcode processing
├── templates/
│   ├── document-grid.php      # Basic grid layout
│   ├── document-list.php      # Basic list layout
│   ├── document-table.php     # Basic table layout
│   ├── document-grouped-grid.php    # Nested folder grid
│   ├── document-grouped-list.php    # Nested folder list
│   └── document-grouped-table.php   # Nested folder table
├── DOCUMENT_LIBRARY_GUIDE.md  # User guide for Document Library CPT
├── DOCUMENT_EDITING_FEATURE.md # Documentation for document editing
└── filebird-frontend-documents.php  # Main plugin file
```

## Complete Class Documentation

### 1. FileBirdFrontendDocuments (Main Plugin Class)
**File**: `filebird-frontend-documents.php`

#### Methods

##### `getInstance()`
- **Purpose**: Singleton pattern to get plugin instance
- **Returns**: `FileBirdFrontendDocuments` instance
- **Usage**: `FileBirdFrontendDocuments::getInstance()`

##### `isFileBirdActive()`
- **Purpose**: Check if FileBird plugin is active
- **Returns**: `boolean`
- **Usage**: Internal method called during initialization

##### `filebirdMissingNotice()`
- **Purpose**: Display admin notice when FileBird is missing
- **Returns**: `void`
- **Usage**: Called via WordPress admin_notices hook

##### `loadDependencies()`
- **Purpose**: Load all required class files
- **Returns**: `void`
- **Usage**: Internal method called during initialization

##### `initComponents()`
- **Purpose**: Initialize all plugin components
- **Returns**: `void`
- **Usage**: Internal method called during initialization

##### `registerHooks()`
- **Purpose**: Register WordPress hooks
- **Returns**: `void`
- **Usage**: Internal method called during initialization

##### `enqueueScripts()`
- **Purpose**: Enqueue frontend scripts and styles
- **Returns**: `void`
- **Usage**: Called via wp_enqueue_scripts hook

##### `loadTextDomain()`
- **Purpose**: Load plugin text domain for translations
- **Returns**: `void`
- **Usage**: Called via init hook

##### `activate()`
- **Purpose**: Plugin activation hook
- **Returns**: `void`
- **Usage**: Called via register_activation_hook

##### `deactivate()`
- **Purpose**: Plugin deactivation hook
- **Returns**: `void`
- **Usage**: Called via register_deactivation_hook

### 2. FileBird_FD_Document_Display Class
**File**: `includes/class-document-display.php`

#### Methods

##### `__construct()`
- **Purpose**: Initialize AJAX handlers
- **Returns**: `void`
- **Usage**: Called automatically when class is instantiated

##### `ajaxGetFolders()`
- **Purpose**: AJAX handler for getting folders
- **Input**: `$_POST['nonce']`
- **Returns**: JSON response with folder data
- **Usage**: Called via AJAX request

##### `ajaxGetDocuments()`
- **Purpose**: AJAX handler for getting documents
- **Input**: `$_POST['folder_id']`, `$_POST['orderby']`, `$_POST['order']`, `$_POST['limit']`, `$_POST['include_subfolders']`
- **Returns**: JSON response with document data
- **Usage**: Called via AJAX request

##### `renderGrid($attachments, $atts)`
- **Purpose**: Render document grid layout
- **Input**: `array $attachments`, `array $atts`
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by template files

##### `renderList($attachments, $atts)`
- **Purpose**: Render document list layout
- **Input**: `array $attachments`, `array $atts`
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by template files

##### `renderTable($attachments, $atts)`
- **Purpose**: Render document table layout
- **Input**: `array $attachments`, `array $atts`
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by template files

##### `getFileTypeIcon($file_type)`
- **Purpose**: Get icon for file type
- **Input**: `string $file_type`
- **Returns**: `string` (HTML for icon)
- **Usage**: Called by render methods

##### `ajaxReplaceDocument()`
- **Purpose**: AJAX handler for replacing documents
- **Input**: `$_POST['attachment_id']`, `$_POST['document_title']`, `$_POST['document_file']`, `$_POST['filebird_fd_nonce']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxRenameDocument()`
- **Purpose**: AJAX handler for renaming documents
- **Input**: `$_POST['attachment_id']`, `$_POST['document_title']`, `$_POST['filebird_fd_nonce']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `canEditDocuments()`
- **Purpose**: Check if user can edit documents
- **Returns**: `boolean`
- **Usage**: Called by template files

##### `getEditButton($attachment_id, $document_title)`
- **Purpose**: Get edit button HTML
- **Input**: `int $attachment_id`, `string $document_title`
- **Returns**: `string` (HTML for edit button)
- **Usage**: Called by template files

### 3. FileBird_FD_Helper Class
**File**: `includes/class-filebird-helper.php`

#### Methods

##### `isFileBirdAvailable()`
- **Purpose**: Check if FileBird plugin is available
- **Returns**: `boolean`
- **Usage**: Called before any FileBird operations

##### `getAllFolders($prepend_default = null)`
- **Purpose**: Get all folders from FileBird
- **Input**: `mixed $prepend_default` (optional)
- **Returns**: `array` of folder objects
- **Usage**: Called by admin interface and folder selectors

##### `getFolderById($folder_id)`
- **Purpose**: Get folder by ID
- **Input**: `int $folder_id`
- **Returns**: `object|null` (folder object or null)
- **Usage**: Called to validate folder existence

##### `getAttachmentIdsByFolderId($folder_id)`
- **Purpose**: Get attachment IDs by folder ID
- **Input**: `int $folder_id`
- **Returns**: `array` of attachment IDs
- **Usage**: Called to get documents in a folder

##### `getAttachmentCountByFolderId($folder_id)`
- **Purpose**: Get attachment count by folder ID
- **Input**: `int $folder_id`
- **Returns**: `int` (count)
- **Usage**: Called to display folder statistics

##### `getFolderTree()`
- **Purpose**: Get hierarchical folder tree
- **Returns**: `array` (nested folder structure)
- **Usage**: Called by admin interface for folder selector

##### `getHierarchicalFolderOptions($include_all = true, $level = 0)`
- **Purpose**: Get hierarchical folder options for dropdowns
- **Input**: `boolean $include_all`, `int $level`
- **Returns**: `array` (hierarchical options)
- **Usage**: Called by admin interface

##### `getSubfolderIds($folder_id)`
- **Purpose**: Get all subfolder IDs recursively
- **Input**: `int $folder_id`
- **Returns**: `array` of subfolder IDs
- **Usage**: Called when including subfolders

##### `getAttachmentsByFolderIdRecursive($folder_id, $args = array())`
- **Purpose**: Get attachments from folder and subfolders
- **Input**: `int $folder_id`, `array $args`
- **Returns**: `array` of attachment objects
- **Usage**: Called by shortcode handler

##### `getAttachmentsGroupedByFolder($folder_id, $args = array())`
- **Purpose**: Get attachments grouped by folder structure
- **Input**: `int $folder_id`, `array $args`
- **Returns**: `array` (grouped attachment structure)
- **Usage**: Called when group_by_folder is true

##### `getAttachmentsByFolderId($folder_id, $args = array())`
- **Purpose**: Get attachments with metadata by folder ID
- **Input**: `int $folder_id`, `array $args`
- **Returns**: `array` of attachment objects with metadata
- **Usage**: Called by various components

##### `getFileSize($attachment_id)`
- **Purpose**: Get file size in human readable format
- **Input**: `int $attachment_id`
- **Returns**: `string` (formatted file size)
- **Usage**: Called to display file sizes

##### `getFolderPath($folder_id)`
- **Purpose**: Get folder path as string
- **Input**: `int $folder_id`
- **Returns**: `string` (folder path)
- **Usage**: Called to display folder hierarchy

##### `folderExists($folder_id)`
- **Purpose**: Check if folder exists
- **Input**: `int $folder_id`
- **Returns**: `boolean`
- **Usage**: Called to validate folder IDs

##### `getFolderOptions($include_all = true)`
- **Purpose**: Get folder options for select dropdown
- **Input**: `boolean $include_all`
- **Returns**: `array` (folder options)
- **Usage**: Called by admin interface

### 4. FileBird_FD_Shortcode_Handler Class
**File**: `includes/class-shortcode-handler.php`

#### Methods

##### `__construct()`
- **Purpose**: Initialize shortcode registration
- **Returns**: `void`
- **Usage**: Called automatically when class is instantiated

##### `registerShortcode()`
- **Purpose**: Register the filebird_docs shortcode
- **Returns**: `void`
- **Usage**: Called via init hook

##### `renderShortcode($atts)`
- **Purpose**: Render the shortcode
- **Input**: `array $atts` (shortcode attributes)
- **Returns**: `string` (HTML output)
- **Usage**: Called by WordPress shortcode system

##### `renderTemplate($layout, $data)`
- **Purpose**: Render the appropriate template
- **Input**: `string $layout`, `array $data`
- **Returns**: `void` (outputs HTML)
- **Usage**: Internal method called by renderShortcode

##### `renderDefaultTemplate($data)`
- **Purpose**: Render default template as fallback
- **Input**: `array $data`
- **Returns**: `void` (outputs HTML)
- **Usage**: Internal method called when template files missing

##### `getShortcodeDocs()`
- **Purpose**: Get shortcode documentation
- **Returns**: `string` (documentation HTML)
- **Usage**: Called by admin interface

### 5. FileBird_FD_Document_Order_Manager Class
**File**: `includes/class-document-order-manager.php`

#### Methods

##### `__construct()`
- **Purpose**: Initialize order manager functionality
- **Returns**: `void`
- **Usage**: Called automatically when class is instantiated

##### `addOrderManagerMenu()`
- **Purpose**: Add order manager menu
- **Returns**: `void`
- **Usage**: Called via admin_menu hook

##### `enqueueOrderManagerScripts($hook)`
- **Purpose**: Enqueue scripts and styles for order manager
- **Input**: `string $hook`
- **Returns**: `void`
- **Usage**: Called via admin_enqueue_scripts hook

##### `ajaxGetDocumentsForOrdering()`
- **Purpose**: AJAX handler for getting documents for ordering
- **Input**: `$_POST['nonce']`, `$_POST['folder_id']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxUpdateDocumentOrder()`
- **Purpose**: AJAX handler for updating document order
- **Input**: `$_POST['nonce']`, `$_POST['folder_id']`, `$_POST['document_order']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxGetFoldersOrderManager()`
- **Purpose**: AJAX handler for getting folders in order manager
- **Input**: `$_POST['nonce']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxGetFolderDocuments()`
- **Purpose**: AJAX handler for getting folder documents
- **Input**: `$_POST['nonce']`, `$_POST['folder_id']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `getDocumentsWithOrder($folder_id)`
- **Purpose**: Get documents with their current order
- **Input**: `int $folder_id`
- **Returns**: `array` of document objects
- **Usage**: Internal method called by AJAX handlers

##### `updateDocumentOrder($folder_id, $document_order)`
- **Purpose**: Update document order in database
- **Input**: `int $folder_id`, `array $document_order`
- **Returns**: `boolean` (success status)
- **Usage**: Internal method called by AJAX handlers

##### `orderManagerPage()`
- **Purpose**: Order manager page content
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by WordPress admin system

### 6. FileBird_FD_Admin Class
**File**: `includes/class-admin.php`

#### Methods

##### `__construct()`
- **Purpose**: Initialize admin functionality
- **Returns**: `void`
- **Usage**: Called automatically when class is instantiated

##### `addAdminMenu()`
- **Purpose**: Add admin menu
- **Returns**: `void`
- **Usage**: Called via admin_menu hook

##### `enqueueAdminScripts($hook)`
- **Purpose**: Enqueue admin scripts and styles
- **Input**: `string $hook`
- **Returns**: `void`
- **Usage**: Called via admin_enqueue_scripts hook

##### `ajaxGetFoldersAdmin()`
- **Purpose**: AJAX handler for getting folders in admin
- **Input**: `$_POST['nonce']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `adminPage()`
- **Purpose**: Admin page content
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by WordPress admin system

### 7. FileBird_FD_Document_Library_CPT Class
**File**: `includes/class-document-library-cpt.php`

#### Methods

##### `__construct()`
- **Purpose**: Initialize document library CPT functionality
- **Returns**: `void`
- **Usage**: Called automatically when class is instantiated

##### `registerPostType()`
- **Purpose**: Register the document_library custom post type
- **Returns**: `void`
- **Usage**: Called via init hook

##### `addMetaBoxes()`
- **Purpose**: Add meta boxes for the custom post type
- **Returns**: `void`
- **Usage**: Called via add_meta_boxes hook

##### `renderSettingsMetaBox($post)`
- **Purpose**: Render settings meta box
- **Input**: `WP_Post $post`
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by WordPress meta box system

##### `renderShortcodeMetaBox($post)`
- **Purpose**: Render shortcode meta box
- **Input**: `WP_Post $post`
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by WordPress meta box system

##### `renderUsageMetaBox($post)`
- **Purpose**: Render usage meta box
- **Input**: `WP_Post $post`
- **Returns**: `void` (outputs HTML)
- **Usage**: Called by WordPress meta box system

##### `saveMetaBoxes($post_id)`
- **Purpose**: Save meta box data
- **Input**: `int $post_id`
- **Returns**: `void`
- **Usage**: Called via save_post hook

##### `registerShortcode()`
- **Purpose**: Register the render_document_library shortcode
- **Returns**: `void`
- **Usage**: Called via init hook

##### `renderDocumentLibrary($atts)`
- **Purpose**: Render document library shortcode
- **Input**: `array $atts`
- **Returns**: `string` (HTML output)
- **Usage**: Called by WordPress shortcode system

##### `enqueueAdminScripts($hook)`
- **Purpose**: Enqueue admin scripts for document library
- **Input**: `string $hook`
- **Returns**: `void`
- **Usage**: Called via admin_enqueue_scripts hook

##### `enqueueEditorStyles()`
- **Purpose**: Enqueue editor styles for frontend
- **Returns**: `void`
- **Usage**: Called via wp_enqueue_scripts hook

##### `ajaxScanLibraryUsage()`
- **Purpose**: AJAX handler for scanning library usage
- **Input**: `$_POST['nonce']`, `$_POST['library_id']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxGetFoldersOrderManager()`
- **Purpose**: AJAX handler for getting folders in order manager
- **Input**: `$_POST['nonce']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxGetDocumentsForOrdering()`
- **Purpose**: AJAX handler for getting documents for ordering
- **Input**: `$_POST['nonce']`, `$_POST['folder_id']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

##### `ajaxUpdateDocumentOrder()`
- **Purpose**: AJAX handler for updating document order
- **Input**: `$_POST['nonce']`, `$_POST['folder_id']`, `$_POST['document_order']`
- **Returns**: JSON response
- **Usage**: Called via AJAX request

### 8. FileBird_FD_Logger Class
**File**: `includes/class-logger.php`

#### Methods

##### `getInstance()`
- **Purpose**: Get singleton instance
- **Returns**: `FileBird_FD_Logger` instance
- **Usage**: `FileBird_FD_Logger::getInstance()`

##### `log($message, $level = 'INFO', $context = array())`
- **Purpose**: Log a message
- **Input**: `string $message`, `string $level`, `array $context`
- **Returns**: `void`
- **Usage**: Called by other classes for logging

##### `info($message, $context = array())`
- **Purpose**: Log info message
- **Input**: `string $message`, `array $context`
- **Returns**: `void`
- **Usage**: Called for informational logging

##### `warning($message, $context = array())`
- **Purpose**: Log warning message
- **Input**: `string $message`, `array $context`
- **Returns**: `void`
- **Usage**: Called for warning logging

##### `error($message, $context = array())`
- **Purpose**: Log error message
- **Input**: `string $message`, `array $context`
- **Returns**: `void`
- **Usage**: Called for error logging

##### `debug($message, $context = array())`
- **Purpose**: Log debug message
- **Input**: `string $message`, `array $context`
- **Returns**: `void`
- **Usage**: Called for debug logging

##### `exception($exception, $context = array())`
- **Purpose**: Log exception
- **Input**: `Exception $exception`, `array $context`
- **Returns**: `void`
- **Usage**: Called for exception logging

##### `getLogContents($lines = 100)`
- **Purpose**: Get log file contents
- **Input**: `int $lines`
- **Returns**: `string` (log contents)
- **Usage**: Called by admin interface

##### `clearLog()`
- **Purpose**: Clear log file
- **Returns**: `boolean` (success status)
- **Usage**: Called by admin interface

##### `getLogFileSize()`
- **Purpose**: Get log file size
- **Returns**: `int` (file size in bytes)
- **Usage**: Called by admin interface

## JavaScript Classes and Functions

### FileBirdFDOrder.OrderManager (order-manager.js)

#### Methods

##### `init()`
- **Purpose**: Initialize order manager
- **Returns**: `void`
- **Usage**: Called on page load

##### `bindEvents()`
- **Purpose**: Bind event handlers
- **Returns**: `void`
- **Usage**: Called by init()

##### `loadFolders()`
- **Purpose**: Load folder tree via AJAX
- **Returns**: `void`
- **Usage**: Called by init()

##### `renderFolderTree(folders)`
- **Purpose**: Render folder tree HTML
- **Input**: `array $folders`
- **Returns**: `void`
- **Usage**: Called by loadFolders()

##### `selectFolder($folderElement)`
- **Purpose**: Handle folder selection
- **Input**: `jQuery $folderElement`
- **Returns**: `void`
- **Usage**: Called by folder click events

##### `loadDocuments(folderId)`
- **Purpose**: Load documents for selected folder
- **Input**: `int $folderId`
- **Returns**: `void`
- **Usage**: Called by selectFolder()

##### `renderDocuments(documents)`
- **Purpose**: Render documents list
- **Input**: `array $documents`
- **Returns**: `void`
- **Usage**: Called by loadDocuments()

##### `saveOrder()`
- **Purpose**: Save document order via AJAX
- **Returns**: `void`
- **Usage**: Called by save button click

##### `resetOrder()`
- **Purpose**: Reset document order to original
- **Returns**: `void`
- **Usage**: Called by reset button click

##### `previewOrder()`
- **Purpose**: Preview current document order
- **Returns**: `void`
- **Usage**: Called by preview button click

## Data Structures and Formats

### Folder Object Structure
```php
object {
    id: int,
    name: string,
    parent: int,
    children: array
}
```

### Attachment Object Structure
```php
object {
    ID: int,
    post_title: string,
    post_date: string,
    menu_order: int,
    file_url: string,
    file_path: string,
    file_type: string,
    file_size: string,
    thumbnail_url: string,
    medium_url: string
}
```

### Shortcode Attributes
```php
array {
    'folder' => string,
    'orderby' => string,
    'order' => string,
    'limit' => int,
    'layout' => string,
    'show_title' => boolean,
    'show_size' => boolean,
    'show_date' => boolean,
    'show_thumbnail' => boolean,
    'columns' => int,
    'class' => string,
    'include_subfolders' => boolean,
    'group_by_folder' => boolean,
    'accordion_states' => string,
    'exclude_folders' => string
}
```

### AJAX Response Format
```php
array {
    'success' => boolean,
    'data' => mixed,
    'message' => string (optional)
}
```

## Error Handling and Logging

### Logger Usage Patterns
```php
$logger = FileBird_FD_Logger::getInstance();

// Log different levels
$logger->info('Operation completed successfully');
$logger->warning('Potential issue detected');
$logger->error('Operation failed', array('context' => 'additional info'));
$logger->debug('Debug information');
$logger->exception($exception, array('context' => 'error context'));
```

### Common Error Scenarios
1. **FileBird not available**: Check with `FileBird_FD_Helper::isFileBirdAvailable()`
2. **Invalid folder ID**: Validate with `FileBird_FD_Helper::folderExists()`
3. **Permission issues**: Check with `current_user_can('manage_options')`
4. **AJAX nonce failures**: Verify with `wp_verify_nonce()`

## Integration Points

### FileBird Plugin Integration
- **Folder access**: `FileBird\Model\Folder::allFolders()`
- **Attachment queries**: `FileBird\Classes\Helpers::getAttachmentIdsByFolderId()`
- **Folder validation**: `FileBird\Model\Folder::findById()`

### WordPress Integration
- **Shortcode registration**: `add_shortcode()`
- **AJAX handlers**: `add_action('wp_ajax_*')`
- **Admin menus**: `add_submenu_page()`
- **Meta boxes**: `add_meta_box()`
- **Custom post types**: `register_post_type()`

### Frontend Integration
- **Script enqueuing**: `wp_enqueue_script()`
- **Style enqueuing**: `wp_enqueue_style()`
- **Localization**: `wp_localize_script()`
- **Nonce creation**: `wp_create_nonce()`

## Performance Considerations

### Caching Strategies
- **Folder tree caching**: Implemented in `FileBird_FD_Helper::getFolderTree()`
- **Attachment metadata**: Cached in `getAttachmentsByFolderId()`
- **Database queries**: Optimized with proper indexing

### Memory Management
- **Large folder handling**: Implemented pagination and limits
- **Recursive operations**: Controlled depth and memory usage
- **File size handling**: Efficient file size calculation

## Security Measures

### Input Validation
- **Nonce verification**: All AJAX requests
- **Permission checks**: User capability validation
- **Data sanitization**: `sanitize_text_field()`, `intval()`, etc.
- **File upload validation**: Type and size restrictions

### Output Escaping
- **HTML escaping**: `esc_html()`, `esc_attr()`
- **URL escaping**: `esc_url()`
- **CSS class sanitization**: `sanitize_html_class()`

## Development Guidelines

### Adding New Features
1. **Create new class file** in `includes/` directory
2. **Register in main plugin file** via `loadDependencies()`
3. **Initialize in `initComponents()`** if needed
4. **Add hooks in constructor** or `registerHooks()`
5. **Update documentation** in PROJECT_CONTEXT.md

### AJAX Handler Pattern
```php
public function ajaxHandler() {
    // 1. Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nonce_name')) {
        wp_die('Security check failed.');
    }
    
    // 2. Check permissions
    if (!current_user_can('required_capability')) {
        wp_die('Insufficient permissions.');
    }
    
    // 3. Validate input
    $input = sanitize_text_field($_POST['input']);
    
    // 4. Process request
    $result = $this->processRequest($input);
    
    // 5. Return response
    if ($result) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Error message');
    }
}
```

### Template Rendering Pattern
```php
public static function renderTemplate($data) {
    // 1. Extract variables
    extract($data);
    
    // 2. Start output buffering
    ob_start();
    
    // 3. Include template file
    include FB_FD_PLUGIN_PATH . 'templates/template-name.php';
    
    // 4. Return buffered content
    return ob_get_clean();
}
```

This comprehensive documentation provides a complete reference for understanding the plugin's architecture, methods, data structures, and usage patterns. It should help resolve the issues you're experiencing with the document reordering feature enhancement.