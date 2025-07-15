=== FileBird Frontend Documents ===
Contributors: Drake Olejniczak
Tags: filebird, documents, shortcode, media, files, frontend
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display FileBird documents on the frontend via shortcode with multiple layout options.

== Description ==

FileBird Frontend Documents is a powerful plugin that extends the FileBird media management plugin to display documents and files on your WordPress frontend using shortcodes.

**Key Features:**

* **Multiple Layout Options**: Grid, List, and Table layouts
* **Flexible Shortcode**: Easy to use with customizable attributes
* **Responsive Design**: Works perfectly on all devices
* **File Type Icons**: Visual indicators for different file types
* **Download Tracking**: Enhanced download buttons with visual feedback
* **Accessibility**: Full keyboard navigation and screen reader support
* **AJAX Support**: Dynamic loading capabilities
* **Customizable Styling**: Modern CSS with easy customization
* **Hierarchical Folder Support**: Display folders in a tree structure
* **Subfolder Integration**: Include documents from subfolders recursively
* **Folder Grouping**: Organize documents by folder structure with section headings

**Shortcode Usage:**

`[filebird_docs folder="123"]`

**Available Attributes:**

* `folder` (required): FileBird folder ID
* `layout`: grid, list, or table (default: grid)
* `columns`: Number of columns for grid layout (default: 3)
* `orderby`: date, title, menu_order, ID (default: date)
* `order`: ASC or DESC (default: DESC)
* `limit`: Number of documents to display (default: -1 for all)
* `show_title`: true or false (default: true)
* `show_size`: true or false (default: false)
* `show_date`: true or false (default: false)
* `show_thumbnail`: true or false (default: true)
* `class`: Additional CSS classes
* `include_subfolders`: true or false (default: false) - Include documents from subfolders
* `group_by_folder`: true or false (default: false) - Group documents by folder structure (requires include_subfolders)

**Examples:**

Basic usage:
`[filebird_docs folder="123"]`

Grid layout with 4 columns:
`[filebird_docs folder="123" layout="grid" columns="4"]`

List layout with file sizes and dates:
`[filebird_docs folder="123" layout="list" show_size="true" show_date="true"]`

Table layout with all information:
`[filebird_docs folder="123" layout="table" show_size="true" show_date="true" show_thumbnail="true"]`

Include subfolders:
`[filebird_docs folder="123" include_subfolders="true"]`

Group by folder structure:
`[filebird_docs folder="123" include_subfolders="true" group_by_folder="true"]`

**Requirements:**

* WordPress 5.0 or higher
* PHP 7.4 or higher
* FileBird plugin (must be installed and activated)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/doc-display-ultimate/` directory, or install the plugin through the WordPress admin screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure FileBird plugin is installed and activated.
4. Use the shortcode `[filebird_docs folder="folder_id"]` in your posts or pages.

== Frequently Asked Questions ==

= What is FileBird? =

FileBird is a WordPress plugin that helps organize media files into folders and categories. This plugin extends FileBird's functionality to display those organized files on the frontend.

= How do I find the folder ID? =

You can find the folder ID in the FileBird admin interface. When you hover over a folder, the ID will be displayed in the browser's status bar or you can inspect the element to find the data attribute.

= Can I use this without FileBird? =

No, this plugin requires FileBird to be installed and activated as it extends FileBird's functionality.

= Is this plugin responsive? =

Yes, the plugin is fully responsive and works on all devices including mobile phones and tablets.

= Can I customize the styling? =

Yes, you can override the CSS by adding custom styles to your theme's stylesheet. All elements have specific CSS classes for easy customization.

= Does this plugin support AJAX? =

Yes, the plugin includes AJAX functionality for dynamic loading of folders and documents.

== Screenshots ==

1. Grid layout displaying documents
2. List layout with file information
3. Table layout with all details
4. Responsive design on mobile devices

== Changelog ==

= 0.1.0 =
* Initial release
* Basic shortcode functionality
* Grid, List, and Table layouts
* File type icons and metadata display
* Responsive design
* AJAX support
* Accessibility features
* Hierarchical folder display in admin
* Subfolder recursive loading support
* Folder grouping with section headings

== Upgrade Notice ==

= 0.1.0 =
Initial release of FileBird Frontend Documents plugin. 