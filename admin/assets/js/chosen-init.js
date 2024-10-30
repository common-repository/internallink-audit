// (function($) {
//
//   'use strict';
//
//   $(document).ready(function() {
//
//     'use strict';
//
//     //initialize chosen on all the select elements
//     var chosenElements = [];
//
//     //Interlinks Options Meta Box --------------------------------------------------------------------------------------
//     addToChosen('ila-enable-ail');
//
//     //Dashboard Menu ---------------------------------------------------------------------------------------------------
//     addToChosen('op');
//     addToChosen('sb');
//     addToChosen('or');
//
//     //AIL Menu ---------------------------------------------------------------------------------------------------------
//     addToChosen('category-id');
//     addToChosen('left-boundary');
//     addToChosen('right-boundary');
//     addToChosen('case-insensitive-search');
//     addToChosen('open-new-tab');
//     addToChosen('use-nofollow');
//
//     //Maintenance Menu -------------------------------------------------------------------------------------------------
//     addToChosen('task');
//
//     //Options Menu -----------------------------------------------------------------------------------------------------
//
//     //AIL
//     addToChosen('cf');
//     addToChosen('ila_default_category_id');
//     addToChosen('ila_default_string_before');
//     addToChosen('ila_default_string_after');
//     addToChosen('ila_default_case_insensitive_search');
//     addToChosen('ila_default_open_new_tab');
//     addToChosen('ila_default_use_nofollow');
//
//     //Suggestions
//     addToChosen('ila_suggestions_titles');
//     addToChosen('ila_suggestions_categories');
//     addToChosen('ila_suggestions_tags');
//     addToChosen('ila_suggestions_post_type');
//
//     //Juice
//     addToChosen('ila_remove_link_to_anchor');
//     addToChosen('ila_remove_url_parameters');
//
//     //Tracking
//     addToChosen('ila_track_internal_links');
//
//     //Analysis
//     addToChosen('ila_set_max_execution_time');
//     addToChosen('ila_set_memory_limit');
//
//     //Advanced
//     addToChosen('ila_default_enable_ail_on_post');
//     addToChosen('ila_ail_test_mode');
//     addToChosen('ila_random_prioritization');
//     addToChosen('ila_ignore_self_ail');
//     addToChosen('ila_general_limit_mode');
//     addToChosen('ila_general_limit_subtract_mil');
//     addToChosen('ila-protected-tags');
//     addToChosen('ila-protected-gutenberg-blocks');
//     addToChosen('ila_pagination_dashboard_menu');
//     addToChosen('ila_pagination_juice_menu');
//     addToChosen('ila_pagination_hits_menu');
//     addToChosen('ila_pagination_ail_menu');
//     addToChosen('ila_pagination_categories_menu');
//
//     $(chosenElements.join(',')).chosen({
//       placeholder_text_multiple: window.objectL10n.chooseAnOptionText,
//     });
//
//     function addToChosen(elementId) {
//
//       if ($('#' + elementId).length && chosenElements.indexOf($('#' + elementId)) === -1) {
//         chosenElements.push('#' + elementId);
//       }
//
//     }
//
//   });
//
// })(window.jQuery);