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