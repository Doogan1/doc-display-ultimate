# FileBird Frontend Documents Plugin - Project Context

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

### Advanced Features
- **Admin folder selector**: Tree-based folder selector in admin interface
- **Subfolder exclusion**: Exclude specific subfolders from display
- **Accordion folders**: Collapsible folder sections with smooth animations
- **Granular accordion control**: Select which folders should be open or closed by default
- **File type icons**: Visual indicators for different file types
- **Download tracking**: Enhanced download buttons with visual feedback
- **AJAX support**: Dynamic loading capabilities

## Technical Architecture

### File Structure
```
doc-display-ultimate/
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin interface styling
│   │   └── frontend.css       # Frontend styling
│   └── js/
│       ├── admin.js           # Admin interface functionality
│       └── frontend.js        # Frontend accordion and interactions
├── includes/
│   ├── class-admin.php        # Admin interface class
│   ├── class-document-display.php  # Main plugin class
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
└── filebird-frontend-documents.php  # Main plugin file
```

### Core Classes

#### FileBird_FD_Document_Display (Main Plugin Class)
- Handles plugin initialization and WordPress hooks
- Manages shortcode registration and processing
- Coordinates between admin and frontend functionality

#### FileBird_FD_Admin (Admin Interface)
- Provides tree-based folder selector in admin
- Handles subfolder exclusion functionality
- Manages shortcode generation and preview

#### FileBird_FD_FileBird_Helper (FileBird Integration)
- Interfaces with FileBird plugin API
- Retrieves folder structures and attachments
- Handles recursive subfolder loading

#### FileBird_FD_Shortcode_Handler (Shortcode Processing)
- Processes shortcode attributes
- Determines appropriate template
- Prepares data for template rendering

## Shortcode System

### Basic Usage
```php
[filebird_docs folder="123"]
```

### Available Attributes
- `folder` (required): FileBird folder ID
- `layout`: grid, list, or table (default: grid)
- `columns`: Number of columns for grid layout (default: 3)
- `orderby`: date, title, menu_order, ID (default: date)
- `order`: ASC or DESC (default: DESC)
- `limit`: Number of documents to display (default: -1 for all)
- `show_title`: true or false (default: true)
- `show_size`: true or false (default: false)
- `show_date`: true or false (default: false)
- `show_thumbnail`: true or false (default: true)
- `class`: Additional CSS classes
- `include_subfolders`: true or false (default: false)
- `group_by_folder`: true or false (default: false)
- `accordion_default`: open or closed (default: closed)
- `accordion_states`: Comma-separated list of folder IDs and their states (e.g., "1:open,2:closed")
- `exclude_folders`: Comma-separated folder IDs to exclude

### Advanced Examples
```php
# Include subfolders with grouping
[filebird_docs folder="123" include_subfolders="true" group_by_folder="true"]

# Table layout with all information
[filebird_docs folder="123" layout="table" show_size="true" show_date="true"]

# Exclude specific subfolders
[filebird_docs folder="123" include_subfolders="true" exclude_folders="456,789"]

# Granular accordion control
[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_states="456:open,789:closed"]
```

## Template System

### Template Types
1. **Basic Templates**: Simple document display without folder structure
   - `document-grid.php`
   - `document-list.php`
   - `document-table.php`

2. **Grouped Templates**: Display with nested folder structure
   - `document-grouped-grid.php`
   - `document-grouped-list.php`
   - `document-grouped-table.php`

### Template Data Structure
```php
$data = [
    'attachments' => [], // Array of folder groups
    'folder' => null,    // FileBird folder object
    'atts' => [],        // Shortcode attributes
    'container_classes' => 'filebird-docs-container'
];
```

### Folder Group Structure
```php
$folder_group = [
    'folder_id' => 123,
    'folder_name' => 'Documents',
    'folder_path' => 'Main/Documents',
    'count' => 5,
    'attachments' => [], // Array of attachment objects
    'children' => []     // Array of nested folder groups
];
```

## Frontend Features

### Accordion System
- **Nested accordions**: Support for unlimited nesting levels
- **State management**: Proper open/closed state handling
- **CSS-based visibility**: Uses `aria-expanded` attribute for accessibility
- **Smooth animations**: CSS transitions for expand/collapse

### Responsive Design
- **Mobile-first approach**: Optimized for mobile devices
- **Flexible grids**: CSS Grid for responsive layouts
- **Touch-friendly**: Large touch targets for mobile users

### Accessibility Features
- **ARIA attributes**: Proper `aria-expanded` and `aria-label` attributes
- **Keyboard navigation**: Full keyboard support for accordions
- **Screen reader support**: Semantic HTML structure
- **Focus management**: Proper focus indicators and management

## Admin Interface

### Folder Selector
- **Tree-based interface**: Hierarchical folder display
- **Search functionality**: Filter folders by name
- **Expand/collapse**: Toggle folder visibility
- **Visual indicators**: Icons for folder states

### Subfolder Exclusion
- **Checkbox interface**: Select subfolders to exclude
- **Bulk operations**: Check all/uncheck all functionality
- **Nested display**: Hierarchical subfolder structure
- **Shortcode generation**: Automatic shortcode attribute updates

### Accordion State Control
- **Radio button interface**: Select which folders should be open or closed by default
- **Bulk operations**: Open all/close all functionality
- **Nested display**: Hierarchical folder structure with state controls
- **Shortcode generation**: Automatic accordion_states attribute updates

## CSS Architecture

### Core Classes
- `.filebird-docs-container`: Main container
- `.filebird-docs-accordion-section`: Accordion wrapper
- `.filebird-docs-accordion-toggle`: Toggle button
- `.filebird-docs-accordion-content`: Content area
- `.filebird-docs-nested-folders`: Nested folder container

### Layout Classes
- `.filebird-docs-grid`: Grid layout container
- `.filebird-docs-list`: List layout container
- `.filebird-docs-table`: Table layout container

### State Classes
- `.filebird-docs-accordion-open`: Open accordion state
- `.filebird-docs-accordion-closed`: Closed accordion state

## JavaScript Functionality

### Frontend JavaScript (frontend.js)
- **Accordion management**: Toggle functionality for nested accordions
- **State initialization**: Proper initial state based on attributes
- **Event handling**: Click, keyboard, and accessibility events
- **AJAX support**: Dynamic loading capabilities

### Admin JavaScript (admin.js)
- **Folder tree management**: Expand/collapse folder tree
- **Search functionality**: Filter folders in real-time
- **Subfolder selection**: Manage exclusion checkboxes
- **Shortcode generation**: Update shortcode preview

## Recent Major Updates

### Granular Accordion Control (Latest)
- **Feature**: Allow users to control which folders are open or closed by default
- **Implementation**: New accordion_states shortcode attribute and admin interface
- **Benefit**: More precise control over accordion behavior for better user experience

### Nested Accordion Fix (Previous)
- **Issue**: Nested accordions showing content when closed
- **Solution**: Enhanced JavaScript logic and CSS rules
- **Result**: Proper state management for nested accordions

### List/Table Template Updates
- **Issue**: Templates not handling nested folder structure
- **Solution**: Added recursive rendering functions
- **Result**: All layout types now support nested folders

### Admin Folder Selector Enhancement
- **Feature**: Tree-based folder selector
- **Functionality**: Search, expand/collapse, visual hierarchy
- **Benefit**: Improved user experience for folder selection

## Integration Points

### FileBird Plugin Integration
- **API calls**: Retrieve folder structures and attachments
- **Hook integration**: WordPress hooks for FileBird events
- **Data processing**: Transform FileBird data for frontend display

### WordPress Integration
- **Shortcode registration**: WordPress shortcode system
- **Admin integration**: WordPress admin interface
- **Asset management**: WordPress enqueue system
- **Translation support**: WordPress i18n system

## Performance Considerations

### Caching Strategy
- **Database queries**: Optimized FileBird queries
- **Template caching**: Efficient template rendering
- **Asset optimization**: Minified CSS and JavaScript

### Scalability
- **Lazy loading**: AJAX-based content loading
- **Pagination**: Support for large document collections
- **Memory management**: Efficient data structure handling

## Security Considerations

### Data Sanitization
- **Input validation**: Shortcode attribute validation
- **Output escaping**: Proper escaping in templates
- **SQL injection prevention**: Prepared statements

### Access Control
- **File access**: Secure file URL generation
- **Admin permissions**: WordPress capability checks
- **Nonce verification**: AJAX security

## Future Development Considerations

### Potential Enhancements
- **Advanced filtering**: Date, type, size filters
- **Search functionality**: Full-text document search
- **Bulk operations**: Download multiple files
- **Analytics**: Download tracking and statistics
- **API endpoints**: REST API for external integration

### Technical Debt
- **Code organization**: Further modularization
- **Testing**: Unit and integration tests
- **Documentation**: API documentation
- **Performance**: Further optimization opportunities

## Troubleshooting Common Issues

### Accordion State Issues
- **Problem**: Nested accordions showing when closed
- **Solution**: Check CSS rules and JavaScript initialization
- **Prevention**: Proper `aria-expanded` attribute management

### Template Rendering Issues
- **Problem**: Documents not displaying in list/table views
- **Solution**: Verify recursive function implementation
- **Prevention**: Consistent template structure across layouts

### FileBird Integration Issues
- **Problem**: Folder data not loading
- **Solution**: Verify FileBird plugin activation and API
- **Prevention**: Proper error handling and fallbacks

This context provides a comprehensive understanding of the FileBird Frontend Documents plugin architecture, functionality, and recent developments for effective LLM consumption and assistance. 