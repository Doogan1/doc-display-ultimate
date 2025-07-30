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
│       └── order-manager.js   # Document order manager functionality
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
└── filebird-frontend-documents.php  # Main plugin file
```

### Core Classes

#### FileBird_FD_Document_Display (Main Plugin Class)
- Handles plugin initialization and WordPress hooks
- Manages shortcode registration and processing
- Coordinates between admin and frontend functionality

#### FileBird_FD_Document_Library_CPT (Document Library Custom Post Type)
- Registers and manages the `document_library` custom post type
- Provides admin interface for library configuration
- Handles meta box saving and shortcode generation
- Integrates document order manager functionality
- Manages frontend editor button display
- Tracks library usage across the site

#### FileBird_FD_Document_Order_Manager (Document Order Manager)
- Provides drag-and-drop document reordering
- Integrated into Document Library CPT settings
- Handles AJAX operations for loading and saving document orders
- Supports real-time preview and reset functionality

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
- Handles Base64-encoded accordion states for complex data

## Shortcode System

### Basic Usage
```php
[filebird_docs folder="123"]
```

### Document Library Usage
```php
[render_document_library id="123"]
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
- `accordion_states`: Base64-encoded JSON string of folder states (e.g., `{"456":"open","789":"closed"}`)
- `exclude_folders`: Comma-separated folder IDs to exclude

### Advanced Examples
```php
# Include subfolders with grouping
[filebird_docs folder="123" include_subfolders="true" group_by_folder="true"]

# Table layout with all information
[filebird_docs folder="123" layout="table" show_size="true" show_date="true"]

# Exclude specific subfolders
[filebird_docs folder="123" include_subfolders="true" exclude_folders="456,789"]

# Granular accordion control (Base64 encoded)
[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_states="eyI0NTYiOiJvcGVuIiwiNzg5IjoiY2xvc2VkIn0="]

# Render a document library
[render_document_library id="123"]
```

## Document Library Custom Post Type

### Features
- **Centralized Management**: Create and configure document displays through WordPress admin
- **Integrated Settings**: All shortcode attributes available through admin interface
- **Frontend Editor Buttons**: Administrators can edit libraries directly from frontend
- **Document Order Manager**: Drag-and-drop reordering when using "Menu Order"
- **Usage Tracking**: See where each library is used across the site
- **Cohesive Styling**: Integrated edit buttons with document display

### Admin Interface
- **Folder Selection**: Tree-based folder selector with search and expand/collapse
- **Layout Configuration**: Grid, List, and Table options with column settings
- **Display Options**: Toggle title, size, date, and thumbnail display
- **Subfolder Management**: Include/exclude subfolders with granular control
- **Accordion States**: Fine-grained control over which folders are open/closed
- **Document Ordering**: Drag-and-drop reordering for menu order displays

### Frontend Integration
- **Editor Buttons**: Blue gradient header with "Edit Library" button for administrators
- **Cohesive Design**: Edit button integrated with document display container
- **State Preservation**: Accordion states maintained when parent folders are closed/reopened

## Accordion State Management

### Technical Implementation
- **Base64 Encoding**: Complex JSON data encoded to prevent shortcode parsing issues
- **State Preservation**: Nested accordion states maintained when parent folders are closed
- **Frontend JavaScript**: Enhanced to preserve accordion states during parent folder operations
- **Admin Interface**: Fine-grained control over individual folder accordion states

### User Experience
- **Granular Control**: Set each folder to open or closed independently
- **State Persistence**: Accordion states preserved during navigation
- **Intuitive Behavior**: Closing parent folders doesn't reset nested accordion states
- **Visual Feedback**: Clear indication of which folders are open/closed

## Recent Updates

### Document Library CPT Integration
- Complete integration of document order manager into Document Library settings
- Enhanced folder selector with automatic expansion and state restoration
- Improved accordion state controls with parent folder inclusion
- Base64 encoding for complex accordion state data in shortcodes

### Frontend Improvements
- Fixed accordion state preservation when closing parent folders
- Enhanced frontend JavaScript to maintain nested accordion states
- Improved user experience with intuitive accordion behavior

### Admin Interface Enhancements
- Automatic folder tree expansion on page load
- State restoration for existing libraries
- Improved subfolder and accordion state controls
- Enhanced document order manager integration

### Code Quality
- Removed all debugging statements and console logs
- Cleaned up temporary test files
- Improved error handling and user feedback
- Enhanced documentation and user guides