<?php
/**
 * Test file for accordion_states functionality
 * 
 * This file demonstrates how to use the new accordion_states attribute
 * to control which folders are open or closed by default.
 */

// Example shortcodes with accordion_states:

// 1. Basic usage - all folders closed by default
echo '[filebird_docs folder="123" include_subfolders="true" group_by_folder="true"]';

// 2. All folders open by default
echo '[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_default="open"]';

// 3. Granular control - specific folders open/closed
echo '[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_states="456:open,789:closed,101:open"]';

// 4. Mixed approach - some folders controlled, others use default
echo '[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_default="closed" accordion_states="456:open"]';

/**
 * Expected behavior:
 * 
 * - If accordion_states is provided, it takes precedence over accordion_default
 * - Folders not specified in accordion_states will use accordion_default
 * - Format: "folder_id:state,folder_id:state" where state is "open" or "closed"
 * - Only works when group_by_folder="true" and include_subfolders="true"
 */
?> 