<?php

namespace Reportico;

/*
Reportico - PHP Reporting Tool
Copyright (C) 2010-2014 Peter Deed

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 * File:        reportico.php
 *
 * This is the core Reportico Reporting Engine. The main
 * reportico class is responsible for coordinating
 * all the other functionality in reading, preparing and
 * executing Reportico reports as well as all the screen
 * handling.
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

/**
 * Class reportico
 *
 * Core functionality that plugs in the database handling,
 * screen handling, XML report definition handling.
 */
class Reportico extends ReporticoObject
{
    public $class = "reportico";
    public $prepare_url;
    public $menu_url;
    public $admin_menu_url;
    public $configure_project_url;
    public $delete_project_url;
    public $create_report_url;

    public $version = "4.6";

    public $name;
    public $rowselection = "all";
    public $parent_query = false;
    public $allow_maintain = "FULL";
    public $embedded_report = false;
    public $allow_debug = true;
    public $user_template = false;
    public $xmlin = false;
    public $xmlout = false;
    public $xmloutfile = false;
    public $xmlintext = false;
    public $xmlinput = false;
    public $sqlinput = false;
    public $datasource;
    public $progress_text = "Ready";
    public $progress_status = "Ready"; // One of READY, WORKING, FINISHED, ERROR
    public $query_statement;
    public $maintain_sql = false;
    public $columns = array();
    public $tables = array();
    public $where_text;
    public $group_text;
    public $table_text;
    public $sql_raw = false;
    public $sql_limit_first = false;
    public $sql_skip_offset = false;
    public $display_order_set = array();
    public $order_set = array();
    public $group_set = array();
    public $groups = array();
    public $pageHeaders = array();
    public $pageFooters = array();
    public $query_count = 0;
    public $expand_col = false;
    public $execute_mode;
    public $match_column = "";
    public $lookup_return_col = false;
    public $lookup_queries = array();
    public $source_type = "database";
    public $source_datasource = false;
    public $source_xml = false;
    public $top_level_query = true;
    public $clone_columns = array();
    public $pre_sql = array();
    public $graphs = array();
    public $clearform = false;
    public $first_criteria_selection = true;
    public $menuitems = array();
    public $dropdown_menu = false;
    public $static_menu = false;
    public $projectitems = array();
    public $target_style = false;
    public $targetFormat = false;
    public $lineno = 0;
    public $groupvals = array();
    public $email_recipients = false;
    public $drilldown_report = false;
    public $forward_url_get_parameters = "";
    public $forward_url_get_parameters_graph = "";
    public $forward_url_get_parameters_dbimage = "";
    public $reportico_ajax_script_url = false;
    public $reportico_ajax_called = false;
    public $reportico_ajax_mode = true;
    public $reportico_ajax_preloaded = false;
    public $clear_reportico_session = false;

    public $target_show_graph = false;
    public $target_show_detail = false;
    public $target_show_group_headers = false;
    public $target_show_group_trailers = false;
    public $target_showColumnHeaders = false;
    public $target_show_criteria = false;

    public $show_form_panel = false;
    public $status_message = "";

    public $framework_parent = false;
    public $framework_type = false;
    public $return_output_to_caller = false;

    public $charting_engine = "PCHART";
    public $charting_engine_html = "NVD3";
    public $pdf_engine = "tcpdf";
    public $pdf_delivery_mode = "DOWNLOAD_SAME_WINDOW";
    public $pdf_engine_file = "ReportFPDF";

    public $projects_folder = "projects";
    public $admin_projects_folder = "projects";
    public $compiled_templates_folder = "templates_c";

    public $attributes = array(
        "ReportTitle" => "Set Report Title",
        "ReportDescription" => false,
        "PageSize" => ".DEFAULT",
        "PageOrientation" => ".DEFAULT",
        "TopMargin" => "",
        "BottomMargin" => "",
        "RightMargin" => "",
        "LeftMargin" => "",
        "pdfFont" => "",
        "pdfFontSize" => "",
        "PreExecuteCode" => "NONE",
        "formBetweenRows" => "solidline",
        //"bodyDisplay" => "show",
        //"graphDisplay" => "show",
        "gridDisplay" => ".DEFAULT",
        "gridSortable" => ".DEFAULT",
        "gridSearchable" => ".DEFAULT",
        "gridPageable" => ".DEFAULT",
        "gridPageSize" => ".DEFAULT",
    );

    public $panels = array();
    public $targets = array();
    public $assignment = array();
    public $criteria_links = array();

    // Admin or normal login
    public $login_type = "NORMAL";

    // Output control
    public $output_skipline = false;
    public $output_allcell_styles = false;
    public $output_criteria_styles = false;
    public $output_header_styles = false;
    public $output_hyperlinks = false;
    public $output_images = false;
    public $output_row_styles = false;
    public $output_page_styles = false;
    public $output_before_form_row_styles = false;
    public $output_after_form_row_styles = false;
    public $output_group_header_styles = false;
    public $output_group_header_label_styles = false;
    public $output_group_header_value_styles = false;
    public $output_group_trailer_styles = false;
    public $output_reportbody_styles = false;
    public $admin_accessible = true;

    // Template Parameters
    public $output_template_parameters = array(
        "show_hide_navigation_menu" => "show",
        "show_hide_dropdown_menu" => "show",
        "show_hide_report_output_title" => "show",
        "show_hide_prepare_section_boxes" => "hide",
        "show_hide_prepare_pdf_button" => "show",
        "show_hide_prepare_html_button" => "show",
        "show_hide_prepare_print_html_button" => "show",
        "show_hide_prepare_csv_button" => "show",
        "show_hide_prepare_page_style" => "show",
        "show_hide_prepare_reset_buttons" => "hide",
        "show_hide_prepare_go_buttons" => "hide",
    );
    // Template Parameters

    // Charsets for in and output
    public $db_charset = false;
    public $output_charset = false;

    // Currently edited links to other reports
    public $reportlink_report = false;
    public $reportlink_report_item = false;
    public $reportlink_or_import = false;

    // Three parameters which can be set from a calling script
    // which can be incorporated into reportic queries
    // For example a calling framework username can
    // be passed so that data can be returned for that
    // user
    public $external_user = false;
    public $external_param1 = false;
    public $external_param2 = false;
    public $external_param3 = false;

    // Initial settings to set default project, report, execute mode. Set by
    // application frameworks embedding reportico
    public $initial_project = false;
    public $initial_execute_mode = false;
    public $initial_report = false;
    public $initial_project_password = false;
    public $initial_output_format = false;
    public $initial_output_style = false;
    public $initial_show_detail = false;
    public $initial_show_graph = false;
    public $initial_show_group_headers = false;
    public $initial_show_group_trailers = false;
    public $initial_showColumnHeaders = false;
    public $initial_show_criteria = false;
    public $initial_execution_parameters = false;
    public $initial_sql = false;

    // Access mode - one of FULL, ALLPROJECTS, ONEPROJECT, REPORTOUTPUT
    public $access_mode = "FULL";

    // Whether to show refresh button on report output
    public $show_refresh_button = false;

    // Whether to show print button on report output
    public $show_print_button = true;

    // Session namespace to use
    public $session_namespace = false;

    // Whether to perform drill downs in their own namespace (normally from embedding in frameworks
    // where reportico namespaces are used within the framework session
    public $drilldown_namespace = false;

    // URL Path to Reportico folder
    public $reportico_url_path = false;

    // Path to Reportico runner for AJAX use or standalone mode
    public $url_path_to_reportico_runner = false;

    // Path to frameworks assets folder
    public $url_path_to_assets = false;

    // Path to public reportico site for help
    public $url_doc_site = "http://www.reportico.org/documentation/";

    // Path to public reportico site
    public $url_site = "http://www.reportico.org/";

    // Path to calling script for form actions
    // In standalone mode will be the reportico runner, otherwise the
    // script in which reportico is embedded
    public $url_path_to_calling_script = false;

    // external user parameters as specified in sql as {USER_PARAM,your_parameter_name}
    // set with $q->user_parameters["your_parameter_name"] = "value";
    public $user_parameters = array();

    // Specify a pdo connection fexternally
    public $external_connection = false;

    public $bootstrap_styles = "3";
    public $jquery_preloaded = false;
    public $bootstrap_preloaded = false;
    public $bootstrap_styling_page = "table table-striped table-condensed";
    public $bootstrap_styling_button_go = "btn btn-success";
    public $bootstrap_styling_button_reset = "btn btn-default";
    public $bootstrap_styling_button_admin = "btn";
    public $bootstrap_styling_button_primary = "btn btn-primary";
    public $bootstrap_styling_button_delete = "btn btn-danger";
    public $bootstrap_styling_dropdown = "form-control";
    //var $bootstrap_styling_checkbox_button = "btn btn-default btn-xs";
    public $bootstrap_styling_checkbox_button = "checkbox-inline";
    public $bootstrap_styling_checkbox = "checkbox";
    public $bootstrap_styling_toolbar_button = "btn";
    public $bootstrap_styling_htabs = "nav nav-justified nav-tabs nav-tabs-justified ";
    public $bootstrap_styling_vtabs = "nav nav-tabs nav-stacked";
    public $bootstrap_styling_design_dropdown = "form-control";
    public $bootstrap_styling_textfield = "form-control";
    public $bootstrap_styling_design_ok = "btn btn-success";
    public $bootstrap_styling_menu_table = "table";
    public $bootstrap_styling_small_button = "btn btn-sm btn-default";

    // Dynamic grids
    public $dynamic_grids = false;
    public $dynamic_grids_sortable = true;
    public $dynamic_grids_searchable = true;
    public $dynamic_grids_paging = false;
    public $dynamic_grids_page_size = 10;

    // Dynamic grids
    public $parent_reportico = false;

    // For laravel ( and other frameworks supporting multiple connections ) specifies
    // an array of available databases to connect ot
    public $available_connections = array();

    // In bootstrap enabled pages, the bootstrap modal is by default used for the quick edit buttons
    // but they can be ignored and reportico's own modal invoked by setting this to true
    public $force_reportico_mini_maintains = false;

    // Array to hold plugins
    public $csrfToken;
    public $plugins = array();

    // Response code to return back
    public $http_response_code = 200;

    // At any point set to true to returnimmediately back, eg after error
    public $return_to_caller = false;

    public function __construct()
    {
        ReporticoObject::__construct();

        $this->parent_query = &$this;

    }

    // Dummy functions for yii to work with Reportico
    public function init()
    {
    }

    public function getIsInitialized()
    {
        return true;
    }
    // End Yii functions

    public function &createGraph()
    {
        $engine = $this->charting_engine;
        if ($this->targetFormat == "HTML") {
            $engine = $this->charting_engine_html;
        }

        if (getRequestItem("targetFormat", "HTML") == "PDF") {
            $engine = $this->charting_engine;
        }

        // Cannot use two forms of charting in the same
        if (!class_exists("reportico_graph", false)) {
            switch ($engine) {
                case "NVD3":
                    $graph = new ChartNVD3($this, "internal");
                    break;

                case "FLOT":
                    $graph = new ChartFLOT($this, "internal");
                    break;

                case "JPGRAPH":
                    $graph = new ChrtJpgraph($this, "internal");
                    break;

                case "PCHART":
                default:
                    $graph = new ChartPchart($this, "internal");
                    break;
            }
        }

        $this->graphs[] = &$graph;
        return $graph;
    }

    /*
     ** In AJAX mode, all links, buttons etc will be served by ajax call to
     ** to runner script or specified ajax script, otherwise they will
     ** call the initial calling script
     */
    public function getActionUrl()
    {
        $calling_script = $this->url_path_to_calling_script;
        if ($this->reportico_ajax_mode) {
            $calling_script = $this->reportico_ajax_script_url;
        }

        return $calling_script;
    }

    public function &getGraphByName($in_query)
    {
        $graphs = array();
        foreach ($this->graphs as $k => $v) {
            if ($v->graph_column == $in_query) {
                $graphs[] = &$this->graphs[$k];
            }
        }
        return $graphs;
    }

    public function queryDisplay()
    {

        foreach ($this->columns as $col) {
            echo $col->query_name;
            echo " " . $col->table_name . "." . $col->column_name;
            echo " " . $col->column_type;
            echo " " . $col->column_length;
            echo " " . $col->in_select;
            echo "<br>\n";
        }

    }

    public function requestDisplay()
    {
        while (list($id, $val) = each($_REQUEST)) {
            echo "<b>$id</b><br>";
            var_dump($val);
            echo "<br>";
        }
    }

    public function setDatasource(&$datasource)
    {
        $this->datasource = &$datasource;
        foreach ($this->columns as $k => $col) {
            $this->columns[$k]->setDatasource($this->datasource);
        }
    }

    public function displayColumns()
    {
        foreach ($this->columns as $k => $col) {
            echo "$k Data: $col->datasource  Name: $col->query_name<br>";
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setLookupReturn
    // -----------------------------------------------------------------------------
    public function setLookupReturn($query_name)
    {

        foreach ($this->columns as $k => $v) {
            $this->columns[$k]->lookup_return_flag = false;
        }
        if ($cl = getQueryColumn($query_name, $this->columns)) {
            $col = &$cl;
            $col->lookup_return_flag = true;
            $this->lookup_return_col = &$col;
        }
    }

    // -----------------------------------------------------------------------------
    // Function : set_column_format
    // -----------------------------------------------------------------------------
    public function setColumnFormat($query_name, $format_type, $format_value)
    {

        $this->checkColumnName("set_column_format", $query_name);
        if ($cl = &getQueryColumn($query_name, $this->columns)) {
            $col = &$cl;
            $col->setFormat($format_type, $format_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : set_group_format
    // -----------------------------------------------------------------------------
    public function setGroupFormat($query_name, $format_type, $format_value)
    {

        $this->checkGroupName("set_group_format", $query_name);
        if (array_key_exists($query_name, $this->group)) {
            $col = &$this->group[$query_name];
            $col->setFormat($format_type, $format_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : add_pre_sql
    // -----------------------------------------------------------------------------
    public function addPreSql($in_sql)
    {
        $this->pre_sql[] = $in_sql;
    }

    // -----------------------------------------------------------------------------
    // Function : setLookupDisplay
    // -----------------------------------------------------------------------------
    public function setLookupDisplay($query_name, $abbrev_name = false)
    {

        if (!$query_name) {
            return;
        }

        if (!$this->checkColumnNameR("setLookupDisplay", $query_name)) {
            handleError("Failure in Lookup Display: Unknown Column Name $query_name");
            return;
        }

        if ($cl = getQueryColumn($query_name, $this->columns)) {
            foreach ($this->columns as $k => $v) {
                $this->columns[$k]->lookup_display_flag = false;
                $this->columns[$k]->lookup_abbrev_flag = false;
            }

            $cl->lookup_display_flag = true;

            if ($abbrev_name) {
                $col2 = getQueryColumn($abbrev_name, $this->columns);
                $col2->lookup_abbrev_flag = true;
            } else {
                $cl->lookup_abbrev_flag = true;
            }

        }
    }

    // -----------------------------------------------------------------------------
    // Function : setLookupExpandMatch
    // -----------------------------------------------------------------------------
    public function setLookupExpandMatch($match_column)
    {
        $this->match_column = $match_column;
    }

    // -----------------------------------------------------------------------------
    // Function : check_page_header_name
    // -----------------------------------------------------------------------------
    public function checkPageHeaderName($in_scope, $in_name)
    {
        if (!array_key_exists($in_name, $this->pageHeaders)) {
            handleError("$in_scope: Group $in_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_page_footer_name
    // -----------------------------------------------------------------------------
    public function checkPageFooterName($in_scope, $in_name)
    {
        if (!array_key_exists($in_name, $this->pageFooters)) {
            handleError("$in_scope: Group $in_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_group_name_r
    // -----------------------------------------------------------------------------
    public function checkGroupNameR($in_scope, $in_column_name)
    {
        if (!($qc = getGroupColumn($in_column_name, $this->groups))) {
            handleError("$in_scope: Group $in_column_name unknown");
            return (false);
        } else {
            return true;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : check_group_name
    // -----------------------------------------------------------------------------
    public function checkGroupName($in_scope, $in_column_name)
    {
        if (!($qc = getGroupColumn($in_column_name, $this->groups))) {
            handleError("$in_scope: Group $in_column_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_column_name_r
    // -----------------------------------------------------------------------------
    public function checkColumnNameR($in_scope, $in_column_name)
    {
        if (!($cl = getQueryColumn($in_column_name, $this->columns))) {
            handleError("$in_scope: Column $in_column_name unknown");
            return false;
        } else {
            return true;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : check_column_name
    // -----------------------------------------------------------------------------
    public function checkColumnName($in_scope, $in_column_name)
    {
        if (!($cl = getQueryColumn($in_column_name, $this->columns))) {
            handleError("$in_scope: Column $in_column_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : getCriteriaValue
    // -----------------------------------------------------------------------------
    public function getCriteriaValue($in_criteria_name, $type = "VALUE", $add_delimiters = true)
    {
        if (!array_key_exists($in_criteria_name, $this->lookup_queries)) {
            return false;
        } else {
            return $this->lookup_queries[$in_criteria_name]->getCriteriaClause(false, false, true, false, false, $add_delimiters);
        }

    }

    // -----------------------------------------------------------------------------
    // Function : get_criteria_by_name
    // -----------------------------------------------------------------------------
    public function getCriteriaByName($in_criteria_name)
    {
        if (!array_key_exists($in_criteria_name, $this->lookup_queries)) {
            return false;
        } else {
            return $this->lookup_queries[$in_criteria_name];
        }

    }

    // -----------------------------------------------------------------------------
    // Function : check_criteria_name
    // -----------------------------------------------------------------------------
    public function checkCriteriaName($in_scope, $in_column_name)
    {
        if (!array_key_exists($in_column_name, $this->lookup_queries)) {
            handleError("$in_scope: Column $in_column_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_criteria_name_r
    // -----------------------------------------------------------------------------
    public function checkCriteriaNameR($in_scope, $in_column_name)
    {
        if (!array_key_exists($in_column_name, $this->lookup_queries)) {
            //handleError("$in_scope: Column $in_column_name unknown");
            return false;
        }
        return true;
    }
    // -----------------------------------------------------------------------------
    // Function : set_criteria_link
    // -----------------------------------------------------------------------------
    public function setCriteriaLink($link_from, $link_to, $clause, $link_number = -1)
    {

        if (!$this->checkCriteriaNameR("set_criteria_link", $link_from)) {
            handleError("Failure in Criteria Link: Unknown Lookup Name $link_from");
            return;
        }
        if (!$this->checkCriteriaNameR("set_criteria_link", $link_to)) {
            handleError("Failure in Criteria Link: Unknown Lookup Name $link_to");
            return;
        }

        //$lf =& $this->columns[$link_from];
        //$lt =& $this->columns[$link_to];

        //$lfq =& $lf->lookup_query;
        //$ltq =& $lt->lookup_query;

        $lfq = &$this->lookup_queries[$link_from]->lookup_query;
        $ltq = &$this->lookup_queries[$link_to]->lookup_query;

        if (!$lfq) {
            handleError("set_criteria_link: No Lookup For $link_from");
        }

        $this->lookup_queries[$link_from]->lookup_query->addCriteriaLink($clause, $link_from, $link_to, $ltq, $link_number);
    }

    // -----------------------------------------------------------------------------
    // Function : add_criteria_link
    // -----------------------------------------------------------------------------
    public function addCriteriaLink($clause, $link_from, $link_to, &$query, $link_number = -1)
    {
        if ($link_number != -1) {
            $this->criteria_links[$link_number] =
            array(
                "clause" => $clause,
                "link_from" => $link_from,
                "tag" => $link_to,
                "query" => &$query,
            );
        } else {
            $this->criteria_links[] =
            array(
                "clause" => $clause,
                "link_from" => $link_from,
                "tag" => $link_to,
                "query" => &$query,
            );
        }

    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaType
    // -----------------------------------------------------------------------------
    public function setCriteriaType($query_name, $criteria_type)
    {

        $this->checkColumnName("set_criteria_ltype", $query_name);
        if (($cl = &getQueryColumn($query_name, $this->columns))) {
            $cl->setCriteriaType($criteria_type);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaRequired
    // -----------------------------------------------------------------------------
    public function setCriteriaRequired($query_name, $criteria_required)
    {
        //$this->check_column_name("set_criteria_lrequired", $query_name);
        if (($cl = getQueryColumn($query_name, $this->lookup_queries))) {
            $cl->setCriteriaRequired($criteria_required);
        } else {
            echo "fail<BR>";
        }

    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaDisplayGroup
    // -----------------------------------------------------------------------------
    public function setCriteriaDisplayGroup($query_name, $criteria_display_group)
    {
        if (($cl = getQueryColumn($query_name, $this->lookup_queries))) {
            $cl->setCriteriaDisplayGroup($criteria_display_group);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaHidden
    // -----------------------------------------------------------------------------
    public function setCriteriaHidden($query_name, $criteria_hidden)
    {
        //$this->check_column_name("set_criteria_lhidden", $query_name);
        if (($cl = getQueryColumn($query_name, $this->lookup_queries))) {
            $cl->setCriteriaHidden($criteria_hidden);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaHelp
    // -----------------------------------------------------------------------------
    public function setCriteriaHelp($query_name, $criteria_help)
    {
        $this->checkCriteriaName("setCriteriaHelp", $query_name);
        if (array_key_exists($query_name, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$query_name];
            $col->setCriteriaHelp($criteria_help);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaLinkReport
    // -----------------------------------------------------------------------------
    public function setCriteriaLinkReport($in_query, $in_report, $in_report_item)
    {
        if (array_key_exists($in_query, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$in_query];
            $col->setCriteriaLinkReport($in_report, $in_report_item);
            $col->setDatasource($this->datasource);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaInput
    // -----------------------------------------------------------------------------
    public function setCriteriaInput($in_query, $in_source, $in_display, $in_expand_display = false, $_use = "")
    {
        if (array_key_exists($in_query, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$in_query];
            $col->setCriteriaInput($in_source, $in_display, $in_expand_display, $_use);
            $col->setDatasource($this->datasource);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaLookup
    // -----------------------------------------------------------------------------
    public function setCriteriaLookup($query_name, &$lookup_query, $in_table, $in_column)
    {
        if (array_key_exists($query_name, $this->lookup_queries)) {
        } else {
            $this->lookup_queries[$query_name] = new CriteriaColumn(
                $this,
                $query_name,
                $in_table,
                $in_column,
                "CHAR",
                0,
                "###.##",
                0
            );
            $this->setCriteriaAttribute($query_name, "column_title", $query_name);
            $lookup_query->setDatasource($this->datasource);
        }

        $this->parent_query = &$this;
        $this->lookup_queries[$query_name]->setCriteriaLookup($lookup_query);
        $this->lookup_queries[$query_name]->first_criteria_selection = $this->first_criteria_selection;
        $this->lookup_queries[$query_name]->lookup_query->parent_query = &$this;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaList
    // -----------------------------------------------------------------------------
    public function setCriteriaList($query_name, $in_list)
    {
        $this->checkCriteriaName("setCriteriaList", $query_name);
        if (array_key_exists($query_name, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$query_name];
            $col->setCriteriaList($in_list);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaDefaults
    // -----------------------------------------------------------------------------
    public function setCriteriaDefaults($query_name, $in_default, $in_delimiter = false)
    {
        if ($in_default || $in_default == "0") {
            $this->checkCriteriaName("setCriteriaDefaults", $query_name);
            if (array_key_exists($query_name, $this->lookup_queries)) {
                $col = &$this->lookup_queries[$query_name];
                $col->setCriteriaDefaults($in_default, $in_delimiter);
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : report_progress
    // -----------------------------------------------------------------------------
    public function reportProgress($in_text, $in_status)
    {
        $this->progress_text = $in_text;
        $this->progress_status = $in_status;

        setReporticoSessionParam("progress_text", $this->progress_text);
        setReporticoSessionParam("progress_status", $this->progress_status);
    }

    // -----------------------------------------------------------------------------
    // Function : setPageHeaderAttribute
    // -----------------------------------------------------------------------------
    public function setPageHeaderAttribute($query_name, $attrib_name, $attrib_value)
    {

        if (!$query_name) {
            $query_name = count($this->pageHeaders) - 1;
        }

        $this->checkPageHeaderName("setPageHeaderAttribute", $query_name);
        if (array_key_exists($query_name, $this->pageHeaders)) {
            $col = &$this->pageHeaders[$query_name];
            $col->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setPageFooterAttribute
    // -----------------------------------------------------------------------------
    public function setPageFooterAttribute($query_name, $attrib_name, $attrib_value)
    {

        if (!$query_name) {
            $query_name = count($this->pageFooters) - 1;
        }

        $this->checkPageFooterName("setPageFooterAttribute", $query_name);
        if (array_key_exists($query_name, $this->pageFooters)) {
            $col = &$this->pageFooters[$query_name];
            $col->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaAttribute
    // -----------------------------------------------------------------------------
    public function setCriteriaAttribute($query_name, $attrib_name, $attrib_value)
    {

        if (array_key_exists($query_name, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$query_name];
            $col->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setColumnAttribute
    // -----------------------------------------------------------------------------
    public function setColumnAttribute($query_name, $attrib_name, $attrib_value)
    {

        $this->checkColumnName("setColumnAttribute", $query_name);
        if (($cl = getQueryColumn($query_name, $this->columns))) {
            $cl->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : addTarget
    // -----------------------------------------------------------------------------
    public function addTarget(&$target)
    {
        $this->targets[] = &$target;
    }

    // -----------------------------------------------------------------------------
    // Function : store_column_results
    // -----------------------------------------------------------------------------
    public function storeColumnResults()
    {
        // Ensure that values returned from database query are placed
        // in the appropriate query column value
        foreach ($this->columns as $k => $col) {
            $this->columns[$k]->old_column_value =
            $this->columns[$k]->column_value;
            $this->columns[$k]->reset_flag = false;
        }
    }

    // -----------------------------------------------------------------------------
    // Function : build_column_results
    // -----------------------------------------------------------------------------
    public function buildColumnResults($result_line)
    {
        // Ensure that values returned from database query are placed
        // in the appropriate query column value
        $ct = 0;
        foreach ($this->columns as $k => $col) {
            if ($col->in_select) {
                $this->debug("selecting $col->query_name in");

                // Oracle returns associated array keys in upper case
                $assoc_key = $col->query_name;
                if (array_key_exists($assoc_key, $result_line)) {
                    $this->debug("exists");
                    $colval = $result_line[$assoc_key];

                    if (is_array($colval)) {
                        continue;
                        //var_dump($colval);
                        //die;
                    }

                    if (is_string($colval)) {
                        $colval = trim($colval);
                    }

                    //$this->debug("$colval");
                } else
                if (array_key_exists(strtoupper($assoc_key), $result_line)) {
                    $this->debug("exists");
                    $colval = $result_line[strtoupper($assoc_key)];

                    if (is_string($colval)) {
                        $colval = trim($colval);
                    }

                    $this->debug("$colval");
                } else {
                    $colval = "NULL";
                }

                $this->columns[$k]->column_value = $colval;
            } else {
                $this->columns[$k]->column_value = $col->query_name;
            }

            $ct++;

        }
    }

    // -----------------------------------------------------------------------------
    // Function : get_execute_mode()
    // -----------------------------------------------------------------------------
    public function getExecuteMode()
    {
        // User clicked Report Dropdown + Go Button
        if (array_key_exists('submit_execute_mode', $_REQUEST)) {
            if (array_key_exists('execute_mode', $_REQUEST)) {
                $this->execute_mode = $_REQUEST['execute_mode'];
            }
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_design_mode', $_REQUEST)) {
            $this->execute_mode = "MAINTAIN";
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_genws_mode', $_REQUEST)) {
            $this->execute_mode = "SOAPSAVE";
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_prepare_mode', $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_criteria_mode', $_REQUEST)) {
            $this->execute_mode = "CRITERIA";
        }

        if (array_key_exists('execute_mode', $_REQUEST)) {
            if ($_REQUEST["execute_mode"] == "MAINTAIN" && $this->allow_maintain != "SAFE"
                && $this->allow_maintain != "FULL" && $this->allow_maintain != "DEMO") {} else {
                $this->execute_mode = $_REQUEST["execute_mode"];
            }
        }

        if (!$this->execute_mode && array_key_exists('submit', $_REQUEST)) {
            $this->execute_mode = "EXECUTE";
        }

        if (!$this->execute_mode && array_key_exists('submitPrepare', $_REQUEST)) {
            $this->execute_mode = "EXECUTE";
        }

        if (!$this->execute_mode && issetReporticoSessionParam("execute_mode")) {
            $this->execute_mode = getReporticoSessionParam("execute_mode");
        }

        // If user has pressed expand then we want to staty in PREPARE mode
        foreach ($_REQUEST as $key => $value) {
            if (preg_match("/^EXPAND_/", $key)) {
                $this->execute_mode = "PREPARE";
                break;
            }
        }

        if (!$this->execute_mode) {
            $this->execute_mode = "MENU";
        }

        if ($this->execute_mode == "MAINTAIN" &&
            $this->allow_maintain != "SAFE" &&
            $this->allow_maintain != "DEMO" &&
            $this->allow_maintain != "FULL" &&
            !$this->parent_query) {
            handleError("Report Maintenance Mode Disallowed");
            $this->execute_mode = "PREPARE";
        }
        if (array_key_exists('execute_mode', $_REQUEST)) {
            if ($_REQUEST["execute_mode"] == "MAINTAIN" && $this->allow_maintain != "SAFE"
                && $this->allow_maintain != "DEMO"
                && $this->allow_maintain != "FULL") {} else {
                $this->execute_mode = $_REQUEST["execute_mode"];
            }
        }

        if (!$this->execute_mode) {
            $this->execute_mode = "MENU";
        }

        // Override mode if specified from ADMIN page
        if (getRequestItem("jump_to_delete_project", "") && array_key_exists("submit_delete_project", $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }

        if (getRequestItem("jump_to_configure_project", "") && array_key_exists("submit_configure_project", $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }

        if (getRequestItem("jump_to_menu_project", "") && array_key_exists("submit_menu_project", $_REQUEST)) {
            $this->execute_mode = "MENU";
        }

        if (getRequestItem("jump_to_design_project", "") && array_key_exists("submit_design_project", $_REQUEST)) {
            $this->xmloutfile = "";
            $this->execute_mode = "PREPARE";
        }

        // If Reset pressed force to Prepare mode
        if (array_key_exists("clearform", $_REQUEST)) {
            setReporticoSessionParam("firstTimeIn", true);
            $this->execute_mode = "PREPARE";
        }

        // If logout pressed then force to MENU mode
        if (array_key_exists("logout", $_REQUEST)) {
            $this->execute_mode = "MENU";
        }

        // If initialised from framework then set mode from there
        if ($this->initial_execute_mode && getReporticoSessionParam("awaiting_initial_defaults")) {
            $this->execute_mode = $this->initial_execute_mode;
        }

        // Maintain execute mode through except for CRITERIA
        if ($this->execute_mode != "CRITERIA") {
            setReporticoSessionParam("execute_mode", $this->execute_mode);
        }

        return ($this->execute_mode);
    }

    // -----------------------------------------------------------------------------
    // Function : set_request_columns()
    // -----------------------------------------------------------------------------
    public function setRequestColumns()
    {
        if (array_key_exists("clearform", $_REQUEST)) {
            $this->clearform = true;
            $this->first_criteria_selection = true;
        }

        // Store filter group open close state
        if (isset($_REQUEST["closedfilters"]) || isset($_REQUEST["openfilters"])) {
            if (isset($_REQUEST["closedfilters"])) {
                setReporticoSessionParam("closedfilters", $_REQUEST["closedfilters"]);
            } else {
                setReporticoSessionParam("closedfilters", false);
            }

            if (isset($_REQUEST["openfilters"])) {
                setReporticoSessionParam("openfilters", $_REQUEST["openfilters"]);
            } else {
                setReporticoSessionParam("openfilters", false);
            }

        }
        //echo ">>>";var_dump(getReporticoSessionParam("openfilters"));

        // If an initial set of parameter values has been set then parameters are being
        // set probably from a framework. In this case we need clear any MANUAL and HIDDEN requests
        // and set MANUAL ones from the external ones
        if ($this->initial_execution_parameters) {
            foreach ($_REQUEST as $k => $v) {
                if (preg_match("/^MANUAL_/", $k) || preg_match("/^HIDDEN_/", $k)) {
                    unset($_REQUEST[$k]);
                }
            }

        }

        $execute_mode = $this->getExecuteMode();
        foreach ($this->lookup_queries as $col) {
            // If this is first time into screen and we have defaults then
            // use these instead
            if (getReporticoSessionParam("firstTimeIn")) {
                $this->lookup_queries[$col->query_name]->column_value =
                $this->lookup_queries[$col->query_name]->defaults;
                if (is_array($this->lookup_queries[$col->query_name]->column_value)) {
                    $this->lookup_queries[$col->query_name]->column_value =
                    implode(",", $this->lookup_queries[$col->query_name]->column_value);
                }

                // Daterange defaults needs to  eb converted to 2 values
                if ($this->lookup_queries[$col->query_name]->criteria_type == "DATERANGE" && !$this->lookup_queries[$col->query_name]->defaults) {
                    $this->lookup_queries[$col->query_name]->defaults = array();
                    $this->lookup_queries[$col->query_name]->defaults[0] = "TODAY-TODAY";
                    $this->lookup_queries[$col->query_name]->defaults[1] = "TODAY";
                    $this->lookup_queries[$col->query_name]->column_value = "TODAY-TODAY";
                }
                if ($this->lookup_queries[$col->query_name]->criteria_type == "DATE" && !$this->lookup_queries[$col->query_name]->defaults) {
                    $this->lookup_queries[$col->query_name]->defaults = array();
                    $this->lookup_queries[$col->query_name]->defaults[0] = "TODAY";
                    $this->lookup_queries[$col->query_name]->defaults[1] = "TODAY";
                    $this->lookup_queries[$col->query_name]->column_value = "TODAY";
                }
                $this->defaults = $this->lookup_queries[$col->query_name]->defaults;
                if (isset($this->defaults)) {
                    if ($this->lookup_queries[$col->query_name]->criteria_type == "DATERANGE") {
                        if (!convertDateRangeDefaultsToDates("DATERANGE",
                            $this->lookup_queries[$col->query_name]->column_value,
                            $this->lookup_queries[$col->query_name]->column_value,
                            $this->lookup_queries[$col->query_name]->column_value2)) {
                            trigger_error("Date default '" . $this->defaults[0] . "' is not a valid date range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                        }

                    }
                    if ($this->lookup_queries[$col->query_name]->criteria_type == "DATE") {
                        $dummy = "";
                        if (!convertDateRangeDefaultsToDates("DATE", $this->defaults[0], $this->range_start, $dummy)) {
                            if (!convertDateRangeDefaultsToDates("DATE",
                                $this->lookup_queries[$col->query_name]->column_value,
                                $this->lookup_queries[$col->query_name]->column_value,
                                $this->lookup_queries[$col->query_name]->column_value2)) {
                                trigger_error("Date default '" . $this->defaults[0] . "' is not a valid date. Should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                            }
                        }

                    }
                }
            }
        }

        if (array_key_exists("clearform", $_REQUEST)) {
            setReporticoSessionParam("firstTimeIn", true);
            setReporticoSessionParam("openfilters", false);
            setReporticoSessionParam("closedfilters", false);
        }

        // Set up show option check box settings

        // If initial form style specified use it
        if ($this->initial_output_style) {
            setReporticoSessionParam("target_style", $this->initial_output_style);
        }

        // If default starting "show" setting provided by calling framework then use them
        if ($this->show_print_button) {
            setReporticoSessionParam("show_print_button", ($this->show_print_button == "show"));
        }

        if ($this->show_refresh_button) {
            setReporticoSessionParam("show_refresh_button", ($this->show_refresh_button == "show"));
        }

        if ($this->initial_show_detail) {
            setReporticoSessionParam("target_show_detail", ($this->initial_show_detail == "show"));
        }

        if ($this->initial_show_graph) {
            setReporticoSessionParam("target_show_graph", ($this->initial_show_graph == "show"));
        }

        if ($this->initial_show_group_headers) {
            setReporticoSessionParam("target_show_group_headers", ($this->initial_show_group_headers == "show"));
        }

        if ($this->initial_show_group_trailers) {
            setReporticoSessionParam("target_show_group_trailers", ($this->initial_show_group_trailers == "show"));
        }

        if ($this->initial_show_criteria) {
            setReporticoSessionParam("target_show_criteria", ($this->initial_show_criteria == "show"));
        }

        $this->target_show_detail = sessionRequestItem("target_show_detail", true, !issetReporticoSessionParam("target_show_detail"));
        $this->target_show_graph = sessionRequestItem("target_show_graph", true, !issetReporticoSessionParam("target_show_graph"));
        $this->target_show_group_headers = sessionRequestItem("target_show_group_headers", true, !issetReporticoSessionParam("target_show_group_headers"));
        $this->target_show_group_trailers = sessionRequestItem("target_show_group_trailers", true, !issetReporticoSessionParam("target_show_group_trailers"));
        $this->target_show_criteria = sessionRequestItem("target_show_criteria", true, !issetReporticoSessionParam("target_show_criteria"));

        if (getReporticoSessionParam("firstTimeIn")
            && !$this->initial_show_detail && !$this->initial_show_graph && !$this->initial_show_group_headers
            && !$this->initial_show_group_trailers && !$this->initial_showColumnHeaders && !$this->initial_show_criteria
        ) {
            // If first time in default output hide/show elements to what is passed in URL params .. if none supplied show all
            if ($this->execute_mode == "EXECUTE") {
                $this->target_show_detail = getRequestItem("target_show_detail", false);
                $this->target_show_graph = getRequestItem("target_show_graph", false);
                $this->target_show_group_headers = getRequestItem("target_show_group_headers", false);
                $this->target_show_group_trailers = getRequestItem("target_show_group_trailers", false);
                $this->target_show_criteria = getRequestItem("target_show_criteria", false);
                if (!$this->target_show_detail && !$this->target_show_graph && !$this->target_show_group_headers
                    && !$this->target_show_group_trailers && !$this->target_showColumnHeaders && !$this->target_show_criteria) {
                    $this->target_show_detail = true;
                    $this->target_show_graph = true;
                    $this->target_show_group_headers = true;
                    $this->target_show_group_trailers = true;
                    $this->target_show_criteria = true;
                }
                setReporticoSessionParam("target_show_detail", $this->target_show_detail);
                setReporticoSessionParam("target_show_graph", $this->target_show_graph);
                setReporticoSessionParam("target_show_group_headers", $this->target_show_group_headers);
                setReporticoSessionParam("target_show_group_trailers", $this->target_show_group_trailers);
                setReporticoSessionParam("target_show_criteria", $this->target_show_criteria);
            } else {
                //$this->target_show_detail = true;
                //$this->target_show_graph = true;
                //$this->target_show_group_headers = true;
                //$this->target_show_group_trailers = true;
                //$this->target_showColumnHeaders = true;
                //$this->target_show_criteria = false;
                //setReporticoSessionParam("target_show_detail",true);
                //setReporticoSessionParam("target_show_graph",true);
                //setReporticoSessionParam("target_show_group_headers",true);
                //setReporticoSessionParam("target_show_group_trailers",true);
                //setReporticoSessionParam("target_showColumnHeaders",true);
                //setReporticoSessionParam("target_show_criteria",false);
            }
        } else {
            // If not first time in, then running report would have come from
            // prepare screen which provides details of what report elements to include
            if ($this->execute_mode == "EXECUTE") {
                $runfromcriteriascreen = getRequestItem("user_criteria_entered", false);
                if ($runfromcriteriascreen) {
                    $this->target_show_detail = getRequestItem("target_show_detail", false);
                    $this->target_show_graph = getRequestItem("target_show_graph", false);
                    $this->target_show_group_headers = getRequestItem("target_show_group_headers", false);
                    $this->target_show_group_trailers = getRequestItem("target_show_group_trailers", false);
                    $this->target_show_criteria = getRequestItem("target_show_criteria", false);
                    if (!$this->target_show_detail && !$this->target_show_graph && !$this->target_show_group_headers
                        && !$this->target_show_group_trailers && !$this->target_showColumnHeaders && !$this->target_show_criteria) {
                        $this->target_show_detail = true;
                        $this->target_show_graph = true;
                        $this->target_show_group_headers = true;
                        $this->target_show_group_trailers = true;
                        $this->target_show_criteria = true;
                    }
                    setReporticoSessionParam("target_show_detail", $this->target_show_detail);
                    setReporticoSessionParam("target_show_graph", $this->target_show_graph);
                    setReporticoSessionParam("target_show_group_headers", $this->target_show_group_headers);
                    setReporticoSessionParam("target_show_group_trailers", $this->target_show_group_trailers);
                    setReporticoSessionParam("target_show_criteria", $this->target_show_criteria);
                }
            }
        }
        if (isset($_REQUEST["target_show_detail"])) {
            setReporticoSessionParam("target_show_detail", $_REQUEST["target_show_detail"]);
        }

        if (isset($_REQUEST["target_show_graph"])) {
            setReporticoSessionParam("target_show_graph", $_REQUEST["target_show_graph"]);
        }

        if (isset($_REQUEST["target_show_group_headers"])) {
            setReporticoSessionParam("target_show_group_headers", $_REQUEST["target_show_group_headers"]);
        }

        if (isset($_REQUEST["target_show_group_trailers"])) {
            setReporticoSessionParam("target_show_group_trailers", $_REQUEST["target_show_group_trailers"]);
        }

        if (isset($_REQUEST["target_show_criteria"])) {
            setReporticoSessionParam("target_show_criteria", $_REQUEST["target_show_criteria"]);
        }

        if (array_key_exists("clearform", $_REQUEST)) {
            return;
        }

        // Fetch current criteria choices from HIDDEN_ section
        foreach ($this->lookup_queries as $col) {
            // criteria name could be a field name or could be "groupby" or the like
            $crit_name = $col->query_name;
            $crit_value = null;

            if (array_key_exists($crit_name, $_REQUEST)) {
                // Since using Select2, we find unselected list boxes still send an empty array with a single character which we dont want to include
                // as a criteria selection
                if (!(is_array($_REQUEST[$col->query_name]) && count($col->query_name) == 1 && $_REQUEST[$col->query_name][0] == "")) {
                    $crit_value = $_REQUEST[$crit_name];
                }

            }

            if (array_key_exists("HIDDEN_" . $crit_name, $_REQUEST)) {
                $crit_value = $_REQUEST["HIDDEN_" . $crit_name];
            }

            // applying multi-column values
            if (array_key_exists("HIDDEN_" . $crit_name . "_FROMDATE", $_REQUEST)) {
                $crit_value_1 = $_REQUEST["HIDDEN_" . $crit_name . "_FROMDATE"];
                $this->lookup_queries[$crit_name]->column_value1 = $crit_value_1;
            }

            if (array_key_exists("HIDDEN_" . $crit_name . "_TODATE", $_REQUEST)) {
                $crit_value_2 = $_REQUEST["HIDDEN_" . $crit_name . "_TODATE"];
                $this->lookup_queries[$crit_name]->column_value2 = $crit_value_2;
            }
            // end applying multi-column values

            if (array_key_exists("EXPANDED_" . $crit_name, $_REQUEST)) {
                $crit_value = $_REQUEST["EXPANDED_" . $crit_name];
            }

            // in case of single column value, we apply it now
            if (!is_null($crit_value)) {
                $this->lookup_queries[$crit_name]->column_value = $crit_value;

                // for groupby criteria, we need to show and hide columns accordingly
                if ($crit_name == 'showfields' || $crit_name == 'groupby') {
                    foreach ($this->columns as $q_col) {
                        //show the column if it matches a groupby value
                        if (in_array($q_col->column_name, $crit_value)) {
                            $q_col->attributes['column_display'] = "show";
                        }
                        // if it doesn't match, hide it if this is the first
                        // groupby column we are going through; otherwise
                        // leave it as it is
                        elseif (!isset($not_first_pass)) {
                            $q_col->attributes['column_display'] = "hide";
                        }
                    }
                    $not_first_pass = true;
                }
            }
        }

        // Fetch current criteria choices from MANUAL_ section
        foreach ($this->lookup_queries as $col) {
            $identified_criteria = false;

            // If an initial set of parameter values has been set then parameters are being
            // set probably from a framework. Use these for setting criteria
            if ($this->initial_execution_parameters) {
                if (isset($this->initial_execution_parameters[$col->query_name])) {
                    $val1 = false;
                    $val2 = false;
                    $criteriaval = $this->initial_execution_parameters[$col->query_name];
                    if ($col->criteria_type == "DATERANGE") {
                        if (!convertDateRangeDefaultsToDates("DATERANGE",
                            $criteriaval,
                            $val1,
                            $val2)) {
                            trigger_error("Date default '" . $criteriaval . "' is not a valid date range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                        } else {
                            $_REQUEST["MANUAL_" . $col->query_name . "_FROMDATE"] = $val1;
                            $_REQUEST["MANUAL_" . $col->query_name . "_TODATE"] = $val2;
                            if (getReporticoSessionParam('latestRequest')) {
                                setReporticoSessionParam("MANUAL_" . $col->query_name . "_FROMDATE", $val1, reporticoNamespace(), "latestRequest");
                                setReporticoSessionParam("MANUAL_" . $col->query_name . "_TODATE", $val2, reporticoNamespace(), "latestRequest");
                            }
                        }
                    } else if ($col->criteria_type == "DATE") {
                        if (!convertDateRangeDefaultsToDates("DATE",
                            $criteriaval,
                            $val1,
                            $val2)) {
                            trigger_error("Date default '" . $criteriaval . "' is not a valid date. Should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                        } else {
                            $_REQUEST["MANUAL_" . $col->query_name . "_FROMDATE"] = $val1;
                            $_REQUEST["MANUAL_" . $col->query_name . "_TODATE"] = $val1;
                            $_REQUEST["MANUAL_" . $col->query_name] = $val1;
                            if (getReporticoSessionParam('latestRequest')) {
                                setReporticoSessionParam("MANUAL_" . $col->query_name . "_FROMDATE", $val1, reporticoNamespace(), "latestRequest");
                                setReporticoSessionParam("MANUAL_" . $col->query_name . "_TODATE", $val1, reporticoNamespace(), "latestRequest");
                                setReporticoSessionParam("MANUAL_" . $col->query_name, $val1, reporticoNamespace(), "latestRequest");
                            }
                        }
                    } else {
                        $_REQUEST["MANUAL_" . $col->query_name] = $criteriaval;
                        if (getReporticoSessionParam('latestRequest')) {
                            setReporticoSessionParam("MANUAL_" . $col->query_name, $val1, reporticoNamespace(), "latestRequest");
                        }
                    }
                }
            }

            // Fetch the criteria value summary if required for displaying
            // the criteria entry summary at top of report
            if ($execute_mode && $execute_mode != "MAINTAIN" && $this->target_show_criteria &&
                ((array_key_exists($col->query_name, $_REQUEST) && !(is_array($_REQUEST[$col->query_name]) && count($col->query_name) == 1 && $_REQUEST[$col->query_name][0] == ""))
                    || array_key_exists("MANUAL_" . $col->query_name, $_REQUEST)
                    || array_key_exists("HIDDEN_" . $col->query_name, $_REQUEST)
                )) {
                $lq = &$this->lookup_queries[$col->query_name];
                if ($lq->criteria_type == "LOOKUP") {
                    $lq->executeCriteriaLookup();
                }

                $lq->criteriaSummaryDisplay();
                $identified_criteria = true;
            }

            if (array_key_exists($col->query_name, $_REQUEST)) {
                // Since using Select2, we find unselected list boxes still send an empty array with a single character which we dont want to include
                // as a criteria selection
                if (!(is_array($_REQUEST[$col->query_name]) && count($col->query_name) == 1 && $_REQUEST[$col->query_name][0] == "")) {
                    $this->lookup_queries[$col->query_name]->column_value =
                    $_REQUEST[$col->query_name];
                }

            }

            if (array_key_exists("MANUAL_" . $col->query_name, $_REQUEST)) {
                $this->lookup_queries[$col->query_name]->column_value =
                $_REQUEST["MANUAL_" . $col->query_name];

                $lq = &$this->lookup_queries[$col->query_name];
                if ($lq->criteria_type == "LOOKUP" && $_REQUEST["MANUAL_" . $col->query_name]) {
                    if (array_key_exists("MANUAL_" . $col->query_name, $_REQUEST)) {
                        foreach ($lq->lookup_query->columns as $k => $col1) {
                            if ($col1->lookup_display_flag) {
                                $lab = &$lq->lookup_query->columns[$k];
                            }

                            if ($col1->lookup_return_flag) {
                                $ret = &$lq->lookup_query->columns[$k];
                            }

                            if ($col1->lookup_abbrev_flag) {
                                $abb = &$lq->lookup_query->columns[$k];
                            }

                        }
                    }

                    if ($abb && $ret && $abb->query_name != $ret->query_name) {
                        if (!$identified_criteria) {
                            $lq->executeCriteriaLookup();
                        }

                        $res = &$lq->lookup_query->targets[0]->results;
                        $choices = $lq->column_value;
                        if (!is_array($choices)) {
                            $choices = explode(',', $choices);
                        }

                        $lq->column_value;
                        $choices = array_unique($choices);
                        $target_choices = array();
                        foreach ($choices as $k => $v) {
                            if (isset($res[$abb->query_name])) {
                                foreach ($res[$abb->query_name] as $k1 => $v1) {
                                    //echo "$v1 / $v<br>";
                                    if ($v1 == $v) {
                                        $target_choices[] = $res[$ret->query_name][$k1];
                                        //echo "$k -> ".$choices[$k]."<BR>";
                                    }
                                }
                            }

                        }
                        $choices = $target_choices;
                        $lq->column_value = implode(",", $choices);

                        if (!$choices) {
                            // Need to set the column value to a arbitrary value when no data found
                            // matching users MANUAL entry .. if left blank then would not bother
                            // creating where clause entry
                            $lq->column_value = "(NOTFOUND)";
                        }
                        $_REQUEST["HIDDEN_" . $col->query_name] = $choices;
                    } else {
                        if (!is_array($_REQUEST["MANUAL_" . $col->query_name])) {
                            $_REQUEST["HIDDEN_" . $col->query_name] = explode(",", $_REQUEST["MANUAL_" . $col->query_name]);
                        } else {
                            $_REQUEST["HIDDEN_" . $col->query_name] = $_REQUEST["MANUAL_" . $col->query_name];
                        }

                    }
                }
            }

            if (array_key_exists($col->query_name . "_FROMDATE_DAY", $_REQUEST)) {
                $this->lookup_queries[$col->query_name]->column_value =
                $this->lookup_queries[$col->query_name]->collateRequestDate(
                    $col->query_name, "FROMDATE",
                    $this->lookup_queries[$col->query_name]->column_value,
                    ReporticoApp::getConfig("prep_dateformat"));
            }

            if (array_key_exists($col->query_name . "_TODATE_DAY", $_REQUEST)) {
                $this->lookup_queries[$col->query_name]->column_value2 =
                $this->lookup_queries[$col->query_name]->collateRequestDate(
                    $col->query_name, "TODATE",
                    $this->lookup_queries[$col->query_name]->column_value2,
                    ReporticoApp::getConfig("prep_dateformat"));
            }

            if (array_key_exists("MANUAL_" . $col->query_name . "_FROMDATE", $_REQUEST)) {
                $this->lookup_queries[$col->query_name]->column_value =
                $_REQUEST["MANUAL_" . $col->query_name . "_FROMDATE"];

            }

            if (array_key_exists("MANUAL_" . $col->query_name . "_TODATE", $_REQUEST)) {
                $this->lookup_queries[$col->query_name]->column_value2 =
                $_REQUEST["MANUAL_" . $col->query_name . "_TODATE"];
            }

            if (array_key_exists("EXPANDED_" . $col->query_name, $_REQUEST)) {
                $this->lookup_queries[$col->query_name]->column_value =
                $_REQUEST["EXPANDED_" . $col->query_name];
            }

        }

        // If external page has supplied an initial output format then use it
        if ($this->initial_output_format) {
            $_REQUEST["targetFormat"] = $this->initial_output_format;
        }

        // If printable HTML requested force output type to HTML
        if (getRequestItem("printable_html")) {
            $_REQUEST["targetFormat"] = "HTML";
        }

        // Prompt user for report destination if target not already set - default to HTML if not set
        if (!array_key_exists("targetFormat", $_REQUEST) && $execute_mode == "EXECUTE") {
            $_REQUEST["targetFormat"] = "HTML";
        }

        if (array_key_exists("targetFormat", $_REQUEST) && $execute_mode == "EXECUTE" && count($this->targets) == 0) {
            $tf = $_REQUEST["targetFormat"];
            if (isset($_GET["targetFormat"])) {
                $tf = $_GET["targetFormat"];
            }

            $this->targetFormat = strtolower($tf);

            if ($this->targetFormat == "pdf") {
                $this->pdf_engine_file = "Report" . strtoupper($this->pdf_engine) . ".php";
                require_once $this->pdf_engine_file;
            } else {
                require_once "Report" . ucwords($this->targetFormat) . ".php";
            }

            $this->targetFormat = strtoupper($tf);
            switch ($tf) {
                case "CSV":
                case "csv":
                case "Microsoft Excel":
                case "EXCEL":
                    $rep = new ReportCsv();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "soap":
                case "SOAP":
                    $rep = new ReportSoapTemplate();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "html":
                case "HTML":
                    $rep = new ReportHtml();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "htmlgrid":
                case "HTMLGRID":
                    $rep = new ReportHtml_grid_template();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "pdf":
                case "PDF":
                    if ($this->pdf_engine == "tcpdf") {
                        $rep = new ReportTCPDF();
                    } else {
                        $rep = new ReportFPDF();
                    }

                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "json":
                case "JSON":
                    $rep = new ReportJson();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "jquerygrid":
                case "JQUERYGRID":
                    $rep = new ReportJQueryGrid();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "xml":
                case "XML":
                    $rep = new ReportXml();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                //case "array" :
                case "ARRAY":
                    $rep = new ReportArray();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                default:
                    // Should not get here
            }
        }

        if (array_key_exists("mailto", $_REQUEST)) {
            $this->email_recipients = $_REQUEST["mailto"];
        }

    }

    // -----------------------------------------------------------------------------
    // Function : login_check
    // -----------------------------------------------------------------------------
    public function loginCheck($smarty)
    {
        if (!$this->datasource) {
            $this->datasource = new ReporticoDataSource($this->external_connection, $this->available_connections);
        }

        $loggedon = false;

        if (ReporticoApp::getConfig("project") == "admin") {
            // Allow access to Admin Page if already logged as admin user, or configuration does not contain
            // an Admin Password (older version of reportico) or Password is blank implying site congired with
            // No Admin Password security or user has just reset password to blank (ie open access )
            if (issetReporticoSessionParam('admin_password') || !ReporticoApp::isSetConfig('admin_password') || (ReporticoApp::isSetConfig('admin_password_reset') && ReporticoApp::getConfig("admin_password_reset") == '')) {
                $loggedon = "ADMIN";
            } else {
                if (array_key_exists("login", $_REQUEST) && isset($_REQUEST['admin_password'])) {
                    // User has supplied an admin password and pressed login
                    if ($_REQUEST['admin_password'] == ReporticoApp::getConfig("admin_password")) {
                        setReporticoSessionParam('admin_password', "1");
                        $loggedon = "ADMIN";
                    } else {
                        $smarty->assign('ADMIN_PASSWORD_ERROR', templateXlate("PASSWORD_ERROR"));
                    }
                }
            }

            if (array_key_exists("adminlogout", $_REQUEST)) {
                unsetReporticoSessionParam('admin_password');
                $loggedon = false;
            }

            // If Admin Password is set to blank then force logged on state to true
            if (ReporticoApp::getConfig("admin_password") == "") {
                setReporticoSessionParam('admin_password', "1");
                $loggedon = true;
            }
            $this->login_type = $loggedon;
            if (!$this->login_type) {
                $this->login_type = "NORMAL";
            }

            return $loggedon;
        }

        $matches = array();
        if (preg_match("/_drilldown(.*)/", reporticoNamespace(), $matches)) {
            $parent_session = $matches[1];
            if (issetReporticoSessionParam("project_password", $parent_session)) {
                setReporticoSessionParam('project_password', getReporticoSessionParam("project_password", $parent_session));
            }
        }

        $project_password = ReporticoApp::getConfig("project_password");
        if (
            (!$project_password) ||
            (issetReporticoSessionParam('admin_password')) ||
            ($this->execute_mode != "MAINTAIN" && issetReporticoSessionParam('project_password') &&
                getReporticoSessionParam('project_password') == $project_password) ||
            (issetReporticoSessionParam('project_password') && getReporticoSessionParam('project_password') == $project_password && $this->allow_maintain == "DEMO")

        ) {
            // After logging on to project allow user access to design mode if user is admin or if we
            // are running in "DEMO" mode
            if (issetReporticoSessionParam('admin_password') || $this->allow_maintain == "DEMO") {
                $loggedon = "DESIGN";
            } else {
                $loggedon = "NORMAL";
            }

        } else {
            // User has attempted to login .. allow access to report PREPARE and MENU modes if user has entered either project
            // or design password or project password is set to blank. Allow access to Design mode if design password is entered
            // or design mode password is blank
            if (isset($_REQUEST['project_password']) || $this->initial_project_password) {
                if ($this->initial_project_password) {
                    $testpassword = $this->initial_project_password;
                } else {
                    $testpassword = $_REQUEST['project_password'];
                }

                if (issetReporticoSessionParam('admin_password') ||
                    ($this->execute_mode != "MAINTAIN" && $testpassword == $project_password)
                ) {
                    setReporticoSessionParam('project_password', $testpassword);
                    $loggedon = true;
                    if (issetReporticoSessionParam('admin_password')) {
                        $loggedon = "DESIGN";
                    } else {
                        $loggedon = "NORMAL";
                    }

                } else {
                    if (isset($_REQUEST["login"])) {
                        $smarty->assign('PROJ_PASSWORD_ERROR', "Error");
                    }

                }
            }
        }

        // User has pressed logout button, default then to MENU mode
        if (array_key_exists("logout", $_REQUEST)) {
            if (issetReporticoSessionParam("admin_password")) {
                unsetReporticoSessionParam('admin_password');
            }
            unsetReporticoSessionParam('project_password');
            setReporticoSessionParam("execute_mode", "MENU");
            $loggedon = false;
            if ($project_password == '') {
                $loggedon = "NORMAL";
            }
        }

        $this->login_type = $loggedon;
        if (!$this->login_type) {
            $this->login_type = "NORMAL";
        }

        return $loggedon;
    }

    // -----------------------------------------------------------------------------
    // Function : build_column_list
    // -----------------------------------------------------------------------------
    public function &buildColumnList()
    {

        $str = "";
        $ct = 0;

        // Build Select Column List
        foreach ($this->columns as $k => $col) {
            if ($col->in_select) {
                if ($ct > 0) {
                    $str .= ",";
                }

                $str .= " ";

                if ($col->table_name) {
                    $str .= $col->table_name . ".";
                }

                $str .= $col->column_name;

                if (($col->query_name)) {
                    $str .= " " . $col->query_name;
                }

                $ct++;
            }
        }
        //die;
        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_order_list
    // -----------------------------------------------------------------------------
    public function &buildOrderList($in_criteria_name)
    {
        $ct = 0;
        $str = "";

        foreach ($this->order_set as $col) {
            if ($ct > 0) {
                $str .= ",";
            } else {
                $ct++;
            }

            $str .= " ";

            if ($col->table_name) {
                $str .= $col->table_name . ".";
                $str .= $col->column_name . " ";
            } else {
                $str .= $col->query_name . " ";
            }
            $str .= $col->order_type;
        }

        // May need to use users custom sort :-
        if (!$in_criteria_name && $orderby = getRequestItem("sidx", "")) {
            if ($orddir = getRequestItem("sord", "")) {
                $str = $orderby . " " . $orddir;
            } else {
                $str = $orderby;
            }

        }

        if ($str) {
            $str = " \nORDER BY " . $str;
        }

        return $str;
    }

    // -----------------------------------------------------------------------------------
    public function &buildLimitOffset()
    {
        $str = "";

        // Handle any user specified FIRST, SKIP ROWS functions
        // Set in the following order :-
        // User specified a limit and offset parameter else
        $limit = getRequestItem("report_limit", "");
        $offset = getRequestItem("report_offset", "");
        // User specified a page and row parameter  which else
        if (!$limit && !$offset) {
            $page = getRequestItem("page", "");
            $rows = getRequestItem("rows", "");
            if ($page && $page > 0 && $rows) {
                $offset = ($page - 1) * $rows;
                $limit = $rows;
            }
        }

        // report contains a default skip and offset definition
        if (!$limit && !$offset) {
            $limit = $this->sql_limit_first;
            $offset = $this->sql_skip_offset;
        }

        if (!$limit && !$offset) {
            return $str;
        }

        if (!$offset) {
            $offset = "0";
        }

        if ($this->datasource->_conn_driver != "pdo_informix" && $this->datasource->_conn_driver != "informix") {
            // Offset without limit doesnt work in Mysql
            if ($this->datasource->_conn_driver == "pdo_mysql") {
                if (!$limit) {
                    $limit = "18446744073709551615";
                }

            }

            $str .= " LIMIT $limit";

            if ($offset) {
                $str .= " OFFSET $offset";
            }

        } else {
            if ($rows) {
                $str .= " FIRST $limit";
            }

            if ($offset) {
                $str = " SKIP $offset" . $str;
            }

        }

        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_where_list
    // -----------------------------------------------------------------------------
    public function &buildWhereList($include_lookups = true)
    {
        // Parse the where text to replace withcriteria values specified
        // with {}
        if ($this->where_text != "AND ") {
            $str = " \nWHERE 1 = 1 " . $this->where_text;
        } else {
            $str = " \nWHERE 1 = 1 ";
        }

        $x = array_keys($this->lookup_queries);

        $parsing = true;

        if ($include_lookups) {
            foreach ($this->lookup_queries as $k => $col) {
                if ($col->column_name) {
                    $str .= $col->getCriteriaClause();
                }
            }
        }

        $str .= " " . $this->group_text;
        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_where_extra_list
    // -----------------------------------------------------------------------------
    public function &buildWhereExtraList($in_is_expanding = false, $criteria_name)
    {
        $str = "";
        $expval = false;
        if ($in_is_expanding) {
            if (array_key_exists("expand_value", $_REQUEST)) {
                if ($_REQUEST["expand_value"] && $this->match_column) {
                    $expval = $_REQUEST["expand_value"];
                }
            }
            if (array_key_exists("MANUAL_" . $criteria_name, $_REQUEST)) {
                $tmpval = $_REQUEST['MANUAL_' . $criteria_name];
                if (strlen($tmpval) > 1 && substr($tmpval, 0, 1) == "?") {
                    $expval = substr($tmpval, 1);
                }

            }

            if ($expval) {
                $str = ' AND ' . $this->match_column . ' LIKE "%' . $expval . '%"';
            }
        } else if ($expval = getRequestItem("reportico_criteria_match", false)) {
            $str = ' AND ' . $this->match_column . ' LIKE "%' . $expval . '%"';
        }

        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_where_criteria_link
    // -----------------------------------------------------------------------------
    public function &buildWhereCriteriaLink($in_is_expanding = false)
    {
        $retval = "";
        foreach ($this->criteria_links as $criteria_link) {
            $clause = $criteria_link["clause"];
            $link = $criteria_link["tag"];
            $query = $criteria_link["query"];

            $params = array();

            if (!array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                if (array_key_exists($link, $_REQUEST)) {
                    $params = $_REQUEST[$link];
                    if (!is_array($params)) {
                        $params = array($params);
                    }

                }
            }

            $hidden_params = array();
            if (!array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                if (array_key_exists("HIDDEN_" . $link, $_REQUEST)) {
                    $hidden_params = $_REQUEST["HIDDEN_" . $link];
                    if (!is_array($hidden_params)) {
                        $hidden_params = array($hidden_params);
                    }

                }
            }

            $manual_params = array();
            if (!array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                if (array_key_exists("MANUAL_" . $link, $_REQUEST)) {
                    $manual_params = explode(',', $_REQUEST["MANUAL_" . $link]);
                    if (!is_array($manual_params)) {
                        $manual_params = array($manual_params);
                    }

                }
            }

            $expanded_params = array();
            if (array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                $expanded_params = $_REQUEST["EXPANDED_" . $link];
                if (!is_array($expanded_params)) {
                    $expanded_params = array($expanded_params);
                }

            }

            $del = "";
            $cls = "";

            // quotedness for in clause is based on return value column
            if ($query) {
                if ($query->lookup_return_col) {
                    $del = $query->lookup_return_col->getValueDelimiter();
                }
            }

            foreach ($hidden_params as $col) {

                if ($col == "(ALL)") {
                    continue;
                }

                if (!$cls) {
                    $cls = $del . $col . $del;
                } else {
                    $cls .= "," . $del . $col . $del;
                }

            }
            foreach ($expanded_params as $col) {
                if ($col == "(ALL)") {
                    continue;
                }

                if (!$cls) {
                    $cls = $del . $col . $del;
                } else {
                    $cls .= "," . $del . $col . $del;
                }

            }

            if ($cls) {
                $retval = " AND $clause IN ( $cls )";
            }

        }

        return ($retval);

    }

    // -----------------------------------------------------------------------------
    // Function : build_table_list
    // -----------------------------------------------------------------------------
    public function &buildTableList()
    {
        $str = " \nFROM " . $this->table_text;
        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : buildQuery
    // -----------------------------------------------------------------------------
    public function buildQuery($in_is_expanding = false, $criteria_name = "", $in_design_mode = false, $no_warnings = false)
    {
        if (!$criteria_name) {
            $this->setRequestColumns();
        }

        $execute_mode = $this->getExecuteMode();

        // Use raw user query in >= Version 2.5
        if ($this->sql_raw) {
            // Now if any of the criteria is an SQLCOMMAND then that should be used for the report data so
            // parse the users command and execute it
            if (!$in_is_expanding && !$criteria_name && !$in_design_mode) {
                foreach ($this->lookup_queries as $key => $col) {
                    if ($col->criteria_type == "SQLCOMMAND") {
                        $this->importSQL($col->column_value);
                    }

                }
            }

            $this->query_statement = $this->sql_raw;

            // Build in criteria items
            $critwhere = "";
            if ($execute_mode != "MAINTAIN") {
                foreach ($this->lookup_queries as $k => $col) {
                    if ($col->column_name) {
                        $critwhere .= $col->getCriteriaClause();
                    }
                }
            }

            // Add in any expand criteria
            $critwhere .= $this->buildWhereExtraList($in_is_expanding, $criteria_name);

            // If user has "Main query column" criteria then parse sql to find
            // where to insert them
            if ($critwhere) {
                $p = new SqlParser($this->query_statement);
                if ($p->parse()) {
                    if ($p->haswhere) {
                        $this->query_statement =
                        substr($this->query_statement, 0, $p->whereoffset) .
                        " 1 = 1" .
                        $critwhere .
                        " AND" .
                        substr($this->query_statement, $p->whereoffset);
                    } else {
                        $this->query_statement =
                        substr($this->query_statement, 0, $p->whereoffset) .
                        " WHERE 1 = 1 " .
                        $critwhere .
                        substr($this->query_statement, $p->whereoffset);
                    }
                }
            }

            // Dont add limits/offset if crtieria query of entering SQL in design mode
            if (!$criteria_name && !$in_design_mode) {
                if ($this->datasource->_conn_driver != "pdo_informix" && $this->datasource->_conn_driver != "informix") {
                    $this->query_statement .= $this->buildLimitOffset();
                }
            }

        } else {
            // Pre Version 2.5 - parts of SQL specified in XML
            $this->query_statement = "SELECT";

            // Dont add limits/offset if crtieria query of entering SQL in design mode
            if (!$criteria_name && !$in_design_mode) {
                if ($this->datasource->_conn_driver == "pdo_informix" || $this->datasource->_conn_driver == "informix") {
                    $this->query_statement .= $this->buildLimitOffset();
                }
            }

            if ($this->rowselection == "unique") {
                if ($this->datasource->_conn_driver == "pdo_informix" || $this->datasource->_conn_driver == "informix") {
                    $this->query_statement .= " UNIQUE";
                } else {
                    $this->query_statement .= " DISTINCT";
                }
            }

            $this->query_statement .= $this->buildColumnList();
            $this->query_statement .= $this->buildTableList();

            if ($execute_mode == "MAINTAIN") {
                $this->query_statement .= $this->buildWhereList(false);
            } else {
                $this->query_statement .= $this->buildWhereList(true);
            }

            $this->query_statement .= $this->buildWhereExtraList($in_is_expanding, $criteria_name);
            $this->query_statement .= $this->buildWhereCriteriaLink($in_is_expanding);
            $this->query_statement .= $this->buildOrderList($criteria_name);

            // Dont add limits/offset if crtieria query of entering SQL in design mode
            if (!$criteria_name && !$in_design_mode) {
                if ($this->datasource->_conn_driver != "pdo_informix" && $this->datasource->_conn_driver != "informix") {
                    $this->query_statement .= $this->buildLimitOffset();
                }
            }

        }

        if ($execute_mode != "MAINTAIN") {
            $this->query_statement = Assignment::reporticoMetaSqlCriteria($this->parent_query, $this->query_statement, false, $no_warnings, $execute_mode);
        }

    }

    // -----------------------------------------------------------------------------
    // Function : createPageHeader
    // -----------------------------------------------------------------------------
    public function createPageHeader(
        $page_header_name = "",
        $line,
        $page_header_text
    ) {
        if (!$page_header_name) {
            $page_header_name = count($this->pageHeaders);
        }

        $this->pageHeaders[$page_header_name] = new ReporticoPageEnd($line, $page_header_text);
    }

    // -----------------------------------------------------------------------------
    // Function : createPageFooter
    // -----------------------------------------------------------------------------
    public function createPageFooter(
        $page_footer_name = "",
        $line,
        $page_footer_text
    ) {
        if (!$page_footer_name) {
            $page_footer_name = count($this->pageFooters);
        }

        $this->pageFooters[$page_footer_name] = new ReporticoPageEnd($line, $page_footer_text);
    }

    // -----------------------------------------------------------------------------
    // Function : createGroup
    // -----------------------------------------------------------------------------
    public function createGroup(
        $query_name = "",
        $in_group = false
    ) {
        $this->groups[] = new ReporticoGroup($query_name, $this);
        end($this->groups);
        $ky = key($this->groups);
        return ($this->groups[$ky]);
    }

    // -----------------------------------------------------------------------------
    // Function : createGroupTrailer
    // -----------------------------------------------------------------------------
    public function createGroupTrailer($query_name, $trailer_column, $value_column, $trailer_custom = false, $show_in_html = "yes", $show_in_pdf = "yes")
    {
        $this->checkGroupName("createGroupTrailer", $query_name);
        //$this->check_column_name("createGroupTrailer", $trailer_column);
        $this->checkColumnName("createGroupTrailer", $value_column);

        $grp = getGroupColumn($query_name, $this->groups);
        $qc = getQueryColumn($value_column, $this->columns);
        //$trl = getQueryColumn($trailer_column, $this->columns )) )
        $grp->addTrailer($trailer_column, $qc, $trailer_custom, $show_in_html, $show_in_pdf);
    }

    // -----------------------------------------------------------------------------
    // Function : delete_group_trailer_by_number
    // -----------------------------------------------------------------------------
    public function deleteGroupTrailerByNumber($query_name, $trailer_number)
    {
        $tn = (int) $trailer_number;
        if (!$this->checkGroupNameR("createGroupTrailer", $query_name)) {
            handleError("Failure in Group Column Trailer: Unknown Group Name $query_name");
            return;
        }

        $grp = getGroupColumn($query_name, $this->groups);

        $ct = 0;
        $k = false;
        $updtr = false;
        foreach ($grp->trailers as $k => $v) {
            if ($ct == $tn) {
                array_splice($grp->trailers, $k, 1);
                return;
            }
            $ct++;
        }

    }
    // -----------------------------------------------------------------------------
    // Function : set_group_trailer_by_number
    // -----------------------------------------------------------------------------
    public function setGroupTrailerByNumber($query_name, $trailer_number, $trailer_column, $value_column, $trailer_custom = false, $show_in_html, $show_in_pdf)
    {
        $tn = (int) $trailer_number;
        if (!$this->checkGroupNameR("createGroupTrailer", $query_name)) {
            handleError("Failure in Group Column Trailer: Unknown Group Name $query_name");
            return;
        }

        if (!$this->checkColumnNameR("createGroupTrailer", $trailer_column)) {
            handleError("Failure in Group Column Trailer: Unknown Column $trailer_column");
            return;
        }

        if (!$this->checkColumnNameR("createGroupTrailer", $value_column)) {
            handleError("Failure in Group Column Trailer: Unknown Column $value_column");
            return;
        }

        //$grp =& $this->groups[$query_name] ;
        $grp = getGroupColumn($query_name, $this->groups);
        $col = getQueryColumn($value_column, $this->columns);

        $trailer = array();
        $trailer["GroupTrailerValueColumn"] = $col;
        $trailer["GroupTrailerDisplayColumn"] = $trailer_column;
        $trailer["GroupTrailerCustom"] = $trailer_custom;
        $trailer["ShowInHTML"] = $show_in_html;
        $trailer["ShowInPDF"] = $show_in_pdf;

        $ct = 0;
        $k = false;
        $updtr = false;
        $looping = true;

        foreach ($grp->trailers as $k => $v) {
            if ($k == $tn) {
                $grp->trailers[$k] = $trailer;
                return;
            }
        }

    }

    // -----------------------------------------------------------------------------
    // Function : createGroupHeader
    // -----------------------------------------------------------------------------
    public function createGroupHeader($query_name, $header_column, $header_custom = false, $show_in_html = "yes", $show_in_pdf = "yes")
    {
        $this->checkGroupName("createGroupHeader", $query_name);
        $this->checkColumnName("createGroupHeader", $header_column);

        $grp = getGroupColumn($query_name, $this->groups);
        $col = getQueryColumn($header_column, $this->columns);
        $grp->addHeader($col, $header_custom, $show_in_html, $show_in_pdf);
    }

    // -----------------------------------------------------------------------------
    // Function : set_group_header_by_number
    // -----------------------------------------------------------------------------
    public function setGroupHeaderByNumber($query_name, $header_number, $header_column, $header_custom, $show_in_html = "yes", $show_in_pdf = "yes")
    {
        $hn = (int) $header_number;
        if (!$this->checkGroupNameR("createGroupHeader", $query_name)) {
            handleError("Failure in Group Column Header: Unknown Group Name $query_name");
            return;
        }

        if (!$this->checkColumnNameR("createGroupHeader", $header_column)) {
            handleError("Failure in Group Column Header: Unknown Column $header_column");
            return;
        }

        $grp = getGroupColumn($query_name, $this->groups);
        $col = getQueryColumn($header_column, $this->columns);
        $header = array();
        $header["GroupHeaderColumn"] = $col;
        $header["GroupHeaderCustom"] = $header_custom;
        $header["ShowInHTML"] = $show_in_html;
        $header["ShowInPDF"] = $show_in_pdf;
        $grp->headers[$hn] = $header;
        //$this->headers[] = $header;
    }

    // -----------------------------------------------------------------------------
    // Function : delete_group_header_by_number
    // -----------------------------------------------------------------------------
    public function deleteGroupHeaderByNumber($query_name, $header_number)
    {

        $hn = (int) $header_number;
        if (!$this->checkGroupNameR("delete_group_header", $query_name)) {
            handleError("Failure in Group Column Header: Unknown Group Name $query_name");
            return;
        }

        $grp = getGroupColumn($query_name, $this->groups);
        array_splice($grp->headers, $hn, 1);
    }

    // -----------------------------------------------------------------------------
    // Function : createGroup_column
    // -----------------------------------------------------------------------------
    public function createGroupColumn(
        $query_name = "",
        $assoc_column = "",
        $summary_columns = "",
        $header_columns = ""
    ) {
        $col = &$this->getColumn($query_name);
        $col->assoc_column = $assoc_column;
        $col->header_columns = explode(',', $header_columns);
        $col->summary_columns = explode(',', $summary_columns);

        $this->group_set[] = &$col;
    }

    // -----------------------------------------------------------------------------
    // Function : create_order_column
    // -----------------------------------------------------------------------------
    public function createOrderColumn(
        $query_name = "",
        $order_type = "ASC"
    ) {
        $col = &$this->getColumn($query_name);

        $order_type = strtoupper($order_type);
        if ($order_type == "UP") {
            $order_type = "ASC";
        }

        if ($order_type == "ASCENDING") {
            $order_type = "ASC";
        }

        if ($order_type == "DOWN") {
            $order_type = "DESC";
        }

        if ($order_type == "DESCENDING") {
            $order_type = "DESC";
        }

        $col->order_type = $order_type;

        $this->order_set[] = &$col;

    }

    // -----------------------------------------------------------------------------
    // Function : remove_group
    // -----------------------------------------------------------------------------
    public function removeGroup(
        $query_name = ""
    ) {
        if (!($grp = getGroupColumn($query_name, $this->groups))) {
            return;
        }

        $cn = 0;
        $ct = 0;
        foreach ($this->groups as $k => $v) {
            if ($k->group_name == $query_name) {
                $cn = $ct;
                break;
            }

            $ct++;
        }

        // finally remove the column
        array_splice($this->groups, $cn, 1);
    }
    // -----------------------------------------------------------------------------
    // Function : remove_column
    // -----------------------------------------------------------------------------
    public function removeColumn(
        $query_name = ""
    ) {
        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            return;
        }

        $ct = 0;
        $cn = 0;
        foreach ($this->columns as $k => $v) {
            if ($v->query_name == $query_name) {
                $cn = $ct;
                break;
            }
            $ct++;
        }

        // Remove all order bys to this column
        $deleting = true;
        while ($deleting) {
            $deleting = false;
            foreach ($this->order_set as $k => $v) {
                if ($v->query_name == $query_name) {
                    array_splice($this->order_set, $k, 1);
                    $deleting = true;
                    break;
                }
            }
        }

        // Remove all assignments to this column
        $deleting = true;
        while ($deleting) {
            $deleting = false;
            foreach ($this->assignment as $k => $v) {
                if ($v->query_name == $query_name) {
                    array_splice($this->assignment, $k, 1);
                    $deleting = true;
                    break;
                }
            }
        }

        // Remove all group headers for this column
        $deleting = true;
        while ($deleting) {
            $deleting = false;
            foreach ($this->groups as $k => $v) {
                foreach ($v->headers as $k1 => $v1) {
                    if ($v1->query_name == $query_name) {
                        array_splice($this->groups[$k]->headers, $k1, 1);
                        $deleting = true;
                        break;
                    }
                }

                $cn1 = 0;
                foreach ($v->trailers as $k1 => $v1) {
                    if ($v1["GroupTrailerDisplayColumn"] == $query_name) {
                        array_splice($this->groups[$k]->trailers, $cn1, 1);
                        $deleting = true;
                        break;
                    }

                    foreach ($v->trailers[$k1] as $k2 => $v2) {
                        if ($v2->query_name == $query_name) {
                            array_splice($this->groups[$k]->trailers[$k1], $k2, 1);
                            $deleting = true;
                            break;
                        }
                    }
                    $cn1++;

                    if ($deleting) {
                        break;
                    }

                }

            }
        }

        // finally remove the column
        array_splice($this->columns, $cn, 1);
    }

    // -----------------------------------------------------------------------------
    // Function : createCriteriaColumn
    // -----------------------------------------------------------------------------
    public function createCriteriaColumn(
        $query_name = "",
        $table_name = "table_name",
        $column_name = "column_name",
        $column_type = "string",
        $column_length = 0,
        $column_mask = "MASK",
        $in_select = true
    ) {
        // Default Query Column Name to Datbase Column Name ( if not set )

        // If the column already exists we are probably importing over the
        // top of an existing query, so just update it
        if ($cl = getQueryColumn($query_name, $this->columns)) {
            $cl->table_name = $table_name;
            $cl->column_name = $column_name;
            $cl->column_type = $column_type;
            $cl->column_length = $column_length;
            $cl->column_mask = $column_mask;
        } else {
            $this->columns[] = new CriteriaColumn
            (
                $this,
                $query_name,
                $table_name,
                $column_name,
                $column_type,
                $column_length,
                $column_mask,
                $in_select
            );
            end($this->columns);
            $ky = key($this->columns);
            $this->display_order_set["itemno"][] = count($this->columns);
            $this->display_order_set["column"][] = &$this->columns[$ky];
        }
    }

    // -----------------------------------------------------------------------------
    // Function : create_query_column
    // -----------------------------------------------------------------------------
    public function createQueryColumn(
        $query_name = "",
        $table_name = "table_name",
        $column_name = "column_name",
        $column_type = "string",
        $column_length = 0,
        $column_mask = "MASK",
        $in_select = true
    ) {
        // Default Query Column Name to Datbase Column Name ( if not set )

        $this->columns[] = new QueryColumn
        (
            $query_name,
            $table_name,
            $column_name,
            $column_type,
            $column_length,
            $column_mask,
            $in_select
        );
        end($this->columns);
        $ky = key($this->columns);
        $this->display_order_set["itemno"][] = count($this->columns);
        $this->display_order_set["column"][] = &$this->columns[$ky];
    }

    // -----------------------------------------------------------------------------
    // Function : setColumnOrder
    // -----------------------------------------------------------------------------
    public function setColumnOrder(
        $query_name = "",
        $order,
        $insert_before = true
    ) {
        //echo "=========================================<br>";
        //echo "set order $query_name - $order<br>";
        // Changes the display order of the column
        // by resetting display_order_set
        reset($this->display_order_set);

        $ct = count($this->display_order_set["itemno"]);
        $c = &$this->display_order_set;
        for ($i = 0; $i < $ct; $i++) {
            if ($c["column"][$i]->query_name == $query_name) {
                if ($c["itemno"][$i] < $order) {
                    //echo $c["itemno"][$i]." up1  ".$c["column"][$i]->query_name." $i<br>";
                    $c["itemno"][$i] = $order + 1;
                } else {
                    //echo $c["itemno"][$i]." set  ".$c["column"][$i]->query_name." $i<br>";
                    $c["itemno"][$i] = $order;
                }
            } else
            if (($c["itemno"][$i] >= $order && $insert_before)
                ||
                ($c["itemno"][$i] > $order && !$insert_before)) {
                //echo $c["itemno"][$i]." up5  ".$c["column"][$i]->query_name." $i<br>";
                $c["itemno"][$i] += 500;
            }
            //else
            //echo $c["itemno"][$i]." leave ".$c["column"][$i]->query_name." $i<br>";
        }

        // Now resort the list
        $n = array_multisort(
            $this->display_order_set["itemno"], SORT_ASC, SORT_NUMERIC,
            $this->display_order_set["column"]
        );

        for ($i = 0; $i < $ct; $i++) {
            $c["itemno"][$i] = $i + 1;
        }
        foreach ($this->display_order_set["itemno"] as $val) {
            $vv = $val - 1;
            //echo " SET $val ",  $this->display_order_set["column"][$vv]->query_name. " - ".$val."/". $this->display_order_set["itemno"][$vv]."<BR>";
            $ct++;
        }

    }

    // Work out whether we are in ajax mode. This is so if either
    // ajax mode has been specified or there is an ajax_script_url specified
    // or reportico has been called by an ajax script using the reportico_ajax_called=1
    // url request parameter
    public function deriveAjaxOperation()
    {
        // Fetch URL path to reportico and set URL path to the runner
        $this->reportico_url_path = getReporticoUrlPath();
        if (!$this->url_path_to_reportico_runner) {
            $this->url_path_to_reportico_runner = $this->reportico_url_path . "run.php";
        }

        // If full ajax mode is requested but no ajax url is passed then defalt the ajax url to the default reportico runner
        registerSessionParam("reportico_ajax_script_url", $this->reportico_ajax_script_url);

        $this->reportico_ajax_script_url = getReporticoSessionParam("reportico_ajax_script_url");
        if ($this->reportico_ajax_script_url && !$this->reportico_ajax_mode) {
            $this->reportico_ajax_mode = true;
        }

        if (!$this->reportico_ajax_script_url) {
            $this->reportico_ajax_script_url = $this->url_path_to_reportico_runner;
        }

        if ($this->reportico_ajax_called && !$this->reportico_ajax_mode) {
            $this->reportico_ajax_mode = true;
        }

        $this->reportico_ajax_preloaded = getRequestItem("reportico_ajax_called", $this->reportico_ajax_preloaded);
        if (getReporticoSessionParam("reportico_ajax_called")) {
            $this->reportico_ajax_mode = true;
        }

        //if ( $this->reportico_ajax_mode )
        //{
        //$this->embedded_report = true;
        //}
    }

    // -----------------------------------------------------------------------------
    // Function : initialize_panels
    //
    // Based on whether Reportico is in criteria entry, report run or other mode
    // Flag what browser panels should be displayed
    // -----------------------------------------------------------------------------
    public function initializePanels($mode)
    {
        $smarty = new \SmartyBC();
        $smarty->template_dir = findBestLocationInIncludePath("templates");
        $smarty->compile_dir = findBestLocationInIncludePath("templates_c");

        $dummy = "";
        $version = $this->version;

        $forward_url_params = sessionRequestItem('forward_url_get_parameters', $this->forward_url_get_parameters);
        $forward_url_params_graph = sessionRequestItem('forward_url_get_parameters_graph', $this->forward_url_get_parameters_graph);
        $forward_url_params_dbimage = sessionRequestItem('forward_url_get_parameters_dbimage', $this->forward_url_get_parameters_dbimage);

        $smarty->assign('REPORTICO_VERSION', $version);
        $smarty->assign('REPORTICO_SITE', $this->url_site);
        $smarty->assign('REPORTICO_CSRF_TOKEN', $this->csrfToken);

        // Assign user parameters to template
        if ($this->user_parameters && is_array($this->user_parameters)) {
            foreach ($this->user_parameters as $k => $v) {
                $param = preg_replace("/ /", "_", $k);
                $smarty->assign('USER_' . $param, $v);
            }
        }

        // Smarty needs to include Javascript if AJAX enabled
        if (!defined('AJAX_ENABLED')) {
            define('AJAX_ENABLED', true);
        }

        $smarty->assign('AJAX_ENABLED', AJAX_ENABLED);

        // Date format for ui Datepicker
        $smarty->assign('AJAX_DATEPICKER_LANGUAGE', getDatepickerLanguage(ReporticoApp::get("language")));
        $smarty->assign('AJAX_DATEPICKER_FORMAT', getDatepickerFormat(ReporticoApp::getConfig("prep_dateformat")));

        $smarty->assign('SHOW_OPEN_LOGIN', false);
        $smarty->assign('DB_LOGGEDON', false);
        $smarty->assign('ADMIN_MENU_URL', false);
        $smarty->assign('CONFIGURE_MENU_URL', false);
        $smarty->assign('CREATE_REPORT_URL', false);
        $smarty->assign('SESSION_ID', reporticoSessionName());

        // Set smarty variables
        $smarty->assign('SCRIPT_SELF', $this->url_path_to_calling_script);

        $smarty->assign('REPORTICO_AJAX_MODE', $this->reportico_ajax_mode);
        $smarty->assign('REPORTICO_AJAX_CALLED', $this->reportico_ajax_called);

        if ($this->url_path_to_assets) {
            $smarty->assign('REPORTICO_URL_DIR', $this->url_path_to_assets);
        } else {
            $smarty->assign('REPORTICO_URL_DIR', $this->reportico_url_path);
        }

        $smarty->assign('REPORTICO_AJAX_RUNNER', $this->reportico_ajax_script_url);

        $smarty->assign('PRINTABLE_HTML', false);
        if (getRequestItem("printable_html")) {
            $smarty->assign('PRINTABLE_HTML', true);
        }

        // In frameworks we dont want to load jquery when its intalled once when the module load
        // so flag this unless specified in new_reportico_window
        $smarty->assign('REPORTICO_STANDALONE_WINDOW', false);
        $smarty->assign('REPORTICO_AJAX_PRELOADED', $this->reportico_ajax_preloaded);
        if (getRequestItem("new_reportico_window", false)) {
            $smarty->assign('REPORTICO_AJAX_PRELOADED', false);
            $smarty->assign('REPORTICO_STANDALONE_WINDOW', true);
        }

        $smarty->assign('SHOW_LOGOUT', false);
        $smarty->assign('SHOW_LOGIN', false);
        $smarty->assign('SHOW_REPORT_MENU', false);
        $smarty->assign('SHOW_SET_ADMIN_PASSWORD', false);
        $smarty->assign('SHOW_OUTPUT', false);
        $smarty->assign('IS_ADMIN_SCREEN', false);
        $smarty->assign('SHOW_DESIGN_BUTTON', false);
        $smarty->assign('SHOW_ADMIN_BUTTON', true);
        $smarty->assign('PROJ_PASSWORD_ERROR', "");
        $smarty->assign('SHOW_PROJECT_MENU_BUTTON', true);
        if ($this->access_mode && ($this->access_mode != "DEMO" && $this->access_mode != "FULL" && $this->access_mode != "ALLPROJECTS" && $this->access_mode != "ONEPROJECT")) {
            $smarty->assign('SHOW_PROJECT_MENU_BUTTON', false);
        }
        $smarty->assign('SHOW_EXPAND', false);
        $smarty->assign('SHOW_CRITERIA', false);
        $smarty->assign('SHOW_EXPANDED', false);
        $smarty->assign('SHOW_MODE_MAINTAIN_BOX', false);
        $smarty->assign('STATUSMSG', '');
        $smarty->assign('ERRORMSG', false);
        $smarty->assign('SET_ADMIN_PASSWORD_INFO', '');
        $smarty->assign('SET_ADMIN_PASSWORD_ERROR', '');
        $smarty->assign('ADMIN_PASSWORD_ERROR', '');
        $smarty->assign('PASSWORD_ERROR', '');
        $smarty->assign('DEMO_MODE', false);
        $smarty->assign('DROPDOWN_MENU_ITEMS', false);

        // Dont allow admin menu buttons to show in demo mode
        if ($this->allow_maintain == "DEMO") {
            $smarty->assign('DEMO_MODE', true);
            $smarty->assign('SHOW_ADMIN_BUTTON', false);
        }

        if (!$this->admin_accessible) {
            $smarty->assign('SHOW_ADMIN_BUTTON', false);
        }

        // Dont show admin button
        if ($this->access_mode && ($this->access_mode != "DEMO" && $this->access_mode != "FULL" && $this->access_mode != "ALLPROJECTS")) {
            $smarty->assign('SHOW_ADMIN_BUTTON', false);
        }

        $partialajaxpath = findBestLocationInIncludePath("partial.php");
        $smarty->assign('AJAX_PARTIAL_RUNNER', $this->reportico_url_path . $partialajaxpath);

        // Use alternative location for js/css/images if specified.
        // Set stylesheet to the reportico bootstrap if bootstrap styles in place
        $this->bootstrap_styles = registerSessionParam("bootstrap_styles", $this->bootstrap_styles);

        // Force reportico modals or decide based on style?
        $this->force_reportico_mini_maintains = registerSessionParam("force_reportico_mini_maintains", $this->force_reportico_mini_maintains);

        $this->url_path_to_assets = registerSessionParam("url_path_to_assets", $this->url_path_to_assets);
        $this->jquery_preloaded = registerSessionParam("jquery_preloaded", $this->jquery_preloaded);
        $this->bootstrap_preloaded = registerSessionParam("bootstrap_preloaded", $this->bootstrap_preloaded);

        //Define the asset dir path
        if ($this->url_path_to_assets) {
            $asset_path = $this->url_path_to_assets;
        } else {
            $asset_path = findBestUrlInIncludePath("assets/notes.txt");
            if ($asset_path) {
                $asset_path = dirname($asset_path);
            }

            $this->url_path_to_assets = $asset_path;
        }

        $smarty->assign('ASSETS_PATH', $asset_path);

        //Define the template dir where we could find specific asset like css
        $theme_dir = findBestUrlInIncludePath('templates/' . $this->getTheme());
        $smarty->assign('THEME_DIR', $theme_dir);

        /*@todo Must be in the theme and not in the code*/
        if (!$this->bootstrap_styles) {
            $csspath = $this->url_path_to_assets . "/css/reportico.css";
            if ($this->url_path_to_assets) {
                $csspath = $this->url_path_to_assets . "/css/reportico.css";
            } else {
                $csspath = $this->reportico_url_path . "/" . findBestUrlInIncludePath("/css/reportico.css");
            }

        } else {
            if ($this->url_path_to_assets) {
                $csspath = $this->url_path_to_assets . "/css/reportico_bootstrap.css";
            } else {
                $csspath = $this->reportico_url_path . "/" . findBestUrlInIncludePath("css/reportico_bootstrap.css");
            }

        }
        $smarty->assign('STYLESHEET', $csspath);
        $smarty->assign('STYLESHEETDIR', dirname($csspath));

        $smarty->assign('REPORTICO_JQUERY_PRELOADED', $this->jquery_preloaded);
        $smarty->assign('BOOTSTRAP_STYLES', $this->bootstrap_styles);
        $smarty->assign('REPORTICO_BOOTSTRAP_PRELOADED', $this->bootstrap_preloaded);
        $smarty->assign('BOOTSTRAP_STYLE_GO_BUTTON', $this->getBootstrapStyle('button_go'));
        $smarty->assign('BOOTSTRAP_STYLE_PRIMARY_BUTTON', $this->getBootstrapStyle('button_primary'));
        $smarty->assign('BOOTSTRAP_STYLE_RESET_BUTTON', $this->getBootstrapStyle('button_reset'));
        $smarty->assign('BOOTSTRAP_STYLE_ADMIN_BUTTON', $this->getBootstrapStyle('button_admin'));
        $smarty->assign('BOOTSTRAP_STYLE_DROPDOWN', $this->getBootstrapStyle('dropdown'));
        $smarty->assign('BOOTSTRAP_STYLE_CHECKBOX_BUTTON', $this->getBootstrapStyle('checkbox_button'));
        $smarty->assign('BOOTSTRAP_STYLE_CHECKBOX', $this->getBootstrapStyle('checkbox'));
        $smarty->assign('BOOTSTRAP_STYLE_TOOLBAR_BUTTON', $this->getBootstrapStyle('toolbar_button'));
        $smarty->assign('BOOTSTRAP_STYLE_MENU_TABLE', $this->getBootstrapStyle('menu_table'));
        $smarty->assign('BOOTSTRAP_STYLE_TEXTFIELD', $this->getBootstrapStyle('textfield'));
        $smarty->assign('BOOTSTRAP_STYLE_SMALL_BUTTON', $this->getBootstrapStyle('small_button'));

        // Set charting engine
        $smarty->assign('REPORTICO_CHARTING_ENGINE', $this->charting_engine_html);

        // Set on/off template elements
        foreach ($this->output_template_parameters as $k => $v) {
            $smarty->assign(strtoupper($k), $v);
        }

        if ($this->url_path_to_assets) {
            $jspath = $this->url_path_to_assets . "/js";
            $smarty->assign('JSPATH', $jspath);
        } else {
            $jspath = findBestUrlInIncludePath("js/reportico.js");
            if ($jspath) {
                $jspath = dirname($jspath);
            }

            $smarty->assign('JSPATH', $this->reportico_url_path . $jspath);
        }
        $this->panels["MAIN"] = new DesignPanel($this, "MAIN");
        $this->panels["MAIN"]->setSmarty($smarty);
        $this->panels["BODY"] = new DesignPanel($this, "BODY");
        $this->panels["TITLE"] = new DesignPanel($this, "TITLE");
        $this->panels["TOPMENU"] = new DesignPanel($this, "TOPMENU");
        $this->panels["MENUBUTTON"] = new DesignPanel($this, "MENUBUTTON");
        $this->panels["LOGIN"] = new DesignPanel($this, "LOGIN");
        $this->panels["SET_ADMIN_PASSWORD"] = new DesignPanel($this, "SET_ADMIN_PASSWORD");
        $this->panels["LOGOUT"] = new DesignPanel($this, "LOGOUT");
        $this->panels["FORM"] = new DesignPanel($this, "FORM");
        $this->panels["MENU"] = new DesignPanel($this, "MENU");
        $this->panels["ADMIN"] = new DesignPanel($this, "ADMIN");
        $this->panels["USERINFO"] = new DesignPanel($this, "USERINFO");
        $this->panels["RUNMODE"] = new DesignPanel($this, "RUNMODE");
        $this->panels["PREPARE"] = new DesignPanel($this, "PREPARE");
        $this->panels["CRITERIA"] = new DesignPanel($this, "CRITERIA");
        $this->panels["CRITERIA_FORM"] = new DesignPanel($this, "CRITERIA_FORM");
        $this->panels["CRITERIA_EXPAND"] = new DesignPanel($this, "CRITERIA_EXPAND");
        $this->panels["MAINTAIN"] = new DesignPanel($this, "MAINTAIN");
        $this->panels["REPORT"] = new DesignPanel($this, "REPORT");
        $this->panels["DESTINATION"] = new DesignPanel($this, "DESTINATION");
        $this->panels["EXECUTE"] = new DesignPanel($this, "EXECUTE");
        $this->panels["STATUS"] = new DesignPanel($this, "STATUS");
        $this->panels["ERROR"] = new DesignPanel($this, "ERROR");

        // Identify which panels are visible by default
        $this->panels["MAIN"]->setVisibility(true);
        $this->panels["BODY"]->setVisibility(true);
        $this->panels["TITLE"]->setVisibility(true);
        $this->panels["TOPMENU"]->setVisibility(true);
        $this->panels["STATUS"]->setVisibility(true);
        $this->panels["ERROR"]->setVisibility(true);

        // Set up a default panel hierarchy
        $this->panels["MAIN"]->addPanel($this->panels["BODY"]);
        $this->panels["BODY"]->addPanel($this->panels["TITLE"]);
        $this->panels["BODY"]->addPanel($this->panels["TOPMENU"]);
        $this->panels["BODY"]->addPanel($this->panels["FORM"]);
        $this->panels["BODY"]->addPanel($this->panels["STATUS"]);
        $this->panels["BODY"]->addPanel($this->panels["ERROR"]);
        $this->panels["FORM"]->addPanel($this->panels["CRITERIA"]);
        $this->panels["FORM"]->addPanel($this->panels["MAINTAIN"]);
        $this->panels["FORM"]->addPanel($this->panels["REPORT"]);
        $this->panels["FORM"]->addPanel($this->panels["MENU"]);
        $this->panels["FORM"]->addPanel($this->panels["ADMIN"]);
        $this->panels["CRITERIA"]->addPanel($this->panels["CRITERIA_FORM"]);
        $this->panels["CRITERIA"]->addPanel($this->panels["CRITERIA_EXPAND"]);
        $this->panels["CRITERIA"]->addPanel($this->panels["DESTINATION"]);
        $this->panels["BODY"]->addPanel($this->panels["REPORT"]);
        $this->panels["TOPMENU"]->addPanel($this->panels["LOGIN"]);
        $this->panels["TOPMENU"]->addPanel($this->panels["SET_ADMIN_PASSWORD"]);
        $this->panels["TOPMENU"]->addPanel($this->panels["USERINFO"]);
        $this->panels["TOPMENU"]->addPanel($this->panels["MENUBUTTON"]);
        $this->panels["TOPMENU"]->addPanel($this->panels["RUNMODE"]);
        $this->panels["TOPMENU"]->addPanel($this->panels["LOGOUT"]);

        // Store any menu page URL, in ajax mode links go through the general ajax link, otherwise go through calling script
        $calling_script = $this->getActionUrl();
        if (preg_match("/\?/", $this->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        $this->prepare_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&amp;reporticoSessionName=" . reporticoSessionName();
        $this->menu_url = $calling_script . "{$url_join_char}execute_mode=MENU&amp;reporticoSessionName=" . reporticoSessionName();
        $this->admin_menu_url = $calling_script . "{$url_join_char}project=admin&amp;execute_mode=MENU&amp;reporticoSessionName=" . reporticoSessionName();
        $this->configure_project_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&amp;xmlin=configureproject.xml&amp;reporticoSessionName=" . reporticoSessionName();
        $this->delete_project_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&amp;xmlin=deleteproject.xml&amp;reporticoSessionName=" . reporticoSessionName();
        $this->create_report_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&amp;xmlin=&amp;reporticoSessionName=" . reporticoSessionName();

        if ($forward_url_params) {
            $this->prepare_url .= "&" . $forward_url_params;
            $this->menu_url .= "&" . $forward_url_params;
            $this->admin_menu_url .= "&" . $forward_url_params;
            $this->configure_project_url .= "&" . $forward_url_params;
            $this->delete_project_url .= "&" . $forward_url_params;
            $this->create_report_url .= "&" . $forward_url_params;
        }
        // ***MENUURL ***if (array_key_exists("menu_url", $_SESSION[reporticoNamespace()]))
        // ***MENUURL ***{
        // ***MENUURL ***$this->menu_url = getReporticoSessionParam("menu_url");
        // ***MENUURL ***}

        // Generate dropdown menu strip in menu or prepare mode
        if (ReporticoApp::getConfig("dropdown_menu") && !$this->dropdown_menu) {
            $this->dropdown_menu = ReporticoApp::getConfig("dropdown_menu");
        }

        if ($this->dropdown_menu && ($mode == "MENU" || $mode == "PREPARE")) {
            $this->generateDropdownMenu($this->dropdown_menu);
            $smarty->assign('DROPDOWN_MENU_ITEMS', $this->dropdown_menu);
        }
        $smarty->assign('MENU_TITLE', ReporticoApp::get('menu_title'));

        if ($mode == "MENU") {
            // Store the URL of thi smenu so it can be referred to
            // in later screens
            // ***MENUURL ***$this->menu_url = $_SERVER["PHP_SELF"];
            // ***MENUURL ***setReporticoSessionParam("menu_url",$this->menu_url);
            $this->panels["MENU"]->setVisibility(true);
            //$this->panels["FORM"]->addPanel($this->panels["MENU"]);
        }

        if ($mode == "EXECUTE") {
            $this->panels["REPORT"]->setVisibility(true);
            //$this->panels["FORM"]->addPanel($this->panels["REPORT"]);
        }

        if ($mode == "MAINTAIN") {
            $this->panels["MAINTAIN"]->setVisibility(true);
            //$this->panels["FORM"]->addPanel($this->panels["MAINTAIN"]);
        }

        if ($mode == "ADMIN") {
            $this->panels["ADMIN"]->setVisibility(true);
            $this->panels["MENU"]->setVisibility(true);
            //$this->panels["FORM"]->addPanel($this->panels["MAINTAIN"]);
        }

        if ($mode == "PREPARE") {
            $this->panels["CRITERIA"]->setVisibility(true);
            $this->panels["CRITERIA_FORM"]->setVisibility(true);
            $this->panels["CRITERIA_EXPAND"]->setVisibility(true);
            $this->panels["DESTINATION"]->setVisibility(true);
            //$this->panels["FORM"]->addPanel($this->panels["CRITERIA"]);
        }

        // Visibility of Login details depends on whether user has provided login
        // details and also whether those details are valid, so set user name
        // and password to use for connection and then attempt to connect
        $this->panels["MENUBUTTON"]->setVisibility(true);
        $this->panels["LOGIN"]->setVisibility(false);
        $this->panels["SET_ADMIN_PASSWORD"]->setVisibility(false);
        $this->panels["LOGOUT"]->setVisibility(true);
        $this->panels["USERINFO"]->setVisibility(true);
        $this->panels["RUNMODE"]->setVisibility(true);

        $smarty->assign('REPORTICO_BOOTSTRAP_MODAL', true);
        if (!$this->bootstrap_styles || $this->force_reportico_mini_maintains) {
            $smarty->assign('REPORTICO_BOOTSTRAP_MODAL', false);
        }

        // If no admin password then force user to enter one and  a language
        if (ReporticoApp::getConfig("project") == "admin" && ReporticoApp::getConfig("admin_password") == "PROMPT") {
            $smarty->assign('LANGUAGES', availableLanguages());
            // New Admin password submitted, attempt to set password and go to MENU option
            if (array_key_exists("submit_admin_password", $_REQUEST)) {
                $smarty->assign('SET_ADMIN_PASSWORD_ERROR',
                    $this->saveAdminPassword($_REQUEST["new_admin_password"], $_REQUEST["new_admin_password2"], $_REQUEST["jump_to_language"]));
            }

            $this->panels["SET_ADMIN_PASSWORD"]->setVisibility(true);
            $smarty->assign('SHOW_SET_ADMIN_PASSWORD', true);
            $this->panels["LOGOUT"]->setVisibility(false);
            $this->panels["MENU"]->setVisibility(false);
            $smarty->assign('SHOW_REPORT_MENU', false);
            if (!ReporticoApp::isSetConfig('admin_password_reset')) {
                return;
            } else {
                $smarty->assign('SHOW_SET_ADMIN_PASSWORD', false);
            }

        }

        $smarty->assign('SHOW_MINIMAINTAIN', false);
        {
            setReporticoSessionParam("loggedin", true);
            if ($this->loginCheck($smarty)) {
                // User has supplied details ( user and password ), so assume that login box should
                // not occur ( user details
                $this->panels["MENUBUTTON"]->setVisibility(true);
                $this->panels["LOGIN"]->setVisibility(false);
                $this->panels["SET_ADMIN_PASSWORD"]->setVisibility(false);
                $this->panels["LOGOUT"]->setVisibility(true);
                $this->panels["USERINFO"]->setVisibility(true);
                $this->panels["FORM"]->setVisibility(true);

                // Show quick edit/mini maintain elements if in design or demo mode
                // unless the report is a reportico configuration report
                if ($this->login_type == "DESIGN" || $this->access_mode == "DEMO") {
                    $smarty->assign('SHOW_MINIMAINTAIN', true);
                }

                if ($this->login_type == "DESIGN") {
                    $this->panels["RUNMODE"]->setVisibility(true);
                } else {
                    $this->panels["RUNMODE"]->setVisibility(false);
                }

                $smarty->assign('SHOW_REPORT_MENU', true);

                // Only show a logout button if a password is in effect
                $project_password = ReporticoApp::getConfig("project_password");
                if ($this->login_type == "DESIGN" || $this->login_type == "ADMIN" || (ReporticoApp::isSetConfig('project_password') && ReporticoApp::getConfig('PROJECT_PASSWORD') != '')) {
                    $smarty->assign('SHOW_LOGOUT', true);
                }

                // Dont show logout button in ALLPROJECTS, ONE PROJECT
                if ($this->access_mode && ($this->access_mode != "DEMO" && $this->access_mode != "FULL" && $this->access_mode != "ALLPROJECTS")) {
                    $smarty->assign('SHOW_LOGOUT', false);
                }

                if ($mode == "PREPARE" && ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml")) {
                    // Dont show database errors if displaying Configure Project prepare page as database connectivity could be wrong
                    // and user will correct it
                } else
                if ($this->datasource->connect() || $mode != "MAINTAIN") {
                    // Store connection session details
                    setReporticoSessionParam("database", $this->datasource->database);
                    setReporticoSessionParam("hostname", $this->datasource->host_name);
                    setReporticoSessionParam("driver", $this->datasource->driver);
                    setReporticoSessionParam("server", $this->datasource->server);
                    setReporticoSessionParam("protocol", $this->datasource->protocol);
                } else {
                    //echo "not connected okay<br>";
                    $this->panels["LOGIN"]->setVisibility(true);
                    $this->panels["SET_ADMIN_PASSWORD"]->setVisibility(false);
                    $this->panels["MENUBUTTON"]->setVisibility(false);
                    $this->panels["LOGOUT"]->setVisibility(false);
                    $this->panels["USERINFO"]->setVisibility(false);
                    $this->panels["RUNMODE"]->setVisibility(true);
                    $this->panels["FORM"]->setVisibility(false);
                    $this->panels["STATUS"]->setVisibility(true);
                    $this->panels["ERROR"]->setVisibility(true);
                }
                //echo "done connecting";
            } else {
                // If not logged in then set first criteria entry to true
                // So when we do get into criteria it will work
                setReporticoSessionParam("firstTimeIn", true);
                setReporticoSessionParam("loggedin", false);

                $this->panels["LOGIN"]->setVisibility(true);
                $this->panels["MENUBUTTON"]->setVisibility(true);
                $this->panels["LOGOUT"]->setVisibility(false);
                $this->panels["USERINFO"]->setVisibility(false);
                $this->panels["RUNMODE"]->setVisibility(false);

                // Dont allow admin design access if access mode is set and not FULL access
                if (ReporticoApp::getConfig("project") == "admin") {
                    if ($this->access_mode && ($this->access_mode != "FULL")) {
                        $this->panels["LOGIN"]->setVisibility(false);
                    }
                }

                // We do want to show the "run project" list in admin mode if not logged in
                if (ReporticoApp::getConfig("project") == "admin") {
                    $this->panels["FORM"]->setVisibility(true);
                } else {
                    $this->panels["FORM"]->setVisibility(false);
                }

            }
        }

        // Turn off design mode if login type anything except design
        if ($this->login_type != "DESIGN") {
            $this->panels["MAINTAIN"]->setVisibility(false);
        }

    }

    // -----------------------------------------------------------------------------
    // If initial starting parameters are given (initial project, access_mode then
    // only use them if this is the first use of the session, other wise clear them
    // -----------------------------------------------------------------------------
    public function handleInitialSettings()
    {
        if (!$this->framework_parent && !getReporticoSessionParam("awaiting_initial_defaults")) {
            $this->initial_project = false;
            $this->initial_sql = false;
            $this->initial_execute_mode = false;
            $this->initial_report = false;
            $this->initial_project_password = false;
            $this->initial_output_style = false;
            $this->initial_output_format = false;
            $this->initial_show_detail = false;
            $this->initial_show_graph = false;
            $this->initial_show_group_headers = false;
            $this->initial_show_group_trailers = false;
            $this->initial_showColumnHeaders = false;
            $this->initial_show_criteria = false;
            $this->initial_execution_parameters = false;
            $this->access_mode = false;
        }
    }

    // -----------------------------------------------------------------------------
    // If initial starting parameters are given (initial project, access_mode then
    // only use them if this is the first use of the session, other wise clear them
    // -----------------------------------------------------------------------------
    public function handledInitialSettings()
    {
        if (getReporticoSessionParam("awaiting_initial_defaults")) {
            setReporticoSessionParam("awaiting_initial_defaults", false);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : initialize_connection
    // -----------------------------------------------------------------------------
    public function initializeConnection()
    {
        return;
    }

    // -----------------------------------------------------------------------------
    // Function : check_criteria_validity
    // Ensures that Mandatory criteria is met
    // -----------------------------------------------------------------------------
    public function checkCriteriaValidity()
    {
        foreach ($this->lookup_queries as $col) {
            if ($col->required == "yes") {
                //handleError( "Mandatory" );
                if (!$this->lookup_queries[$col->query_name]->column_value) {
                    if (true || getRequestItem("new_reportico_window", false)) {
                        $this->http_response_code = 500;
                        $this->return_to_caller = true;
                        handleError(templateXlate("REQUIRED_CRITERIA") . " - " . swTranslate($this->lookup_queries[$col->query_name]->deriveAttribute("column_title", "")));
                        //echo '<div class="swError">'.templateXlate("REQUIRED_CRITERIA")." - ".swTranslate($this->lookup_queries[$col->query_name]->deriveAttribute("column_title", ""))."</div>";
                        return;
                    } else {
                        handleError(templateXlate("REQUIRED_CRITERIA") . " - " . swTranslate($this->lookup_queries[$col->query_name]->deriveAttribute("column_title", ""))
                            , E_USER_ERROR);
                    }

                }
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : handle_xml_input
    // -----------------------------------------------------------------------------
    public function handleXmlQueryInput($mode = false)
    {
        if (!$this->top_level_query) {
            return;
        }

        if ($mode == "MENU" && issetReporticoSessionParam("xmlin"))
        //if ( $mode == "MENU" && array_key_exists("xmlin", $_SESSION[reporticoNamespace()]) )
        {
            unsetReporticoSessionParam("xmlin");
        }

        if ($mode == "ADMIN" && issetReporticoSessionParam("xmlin"))
        //if ( $mode == "ADMIN" && array_key_exists("xmlin", $_SESSION[reporticoNamespace()]) )
        {
            unsetReporticoSessionParam("xmlin");
        }

        // See if XML needs to be read in
        $this->xmlinput = false;
        $this->sqlinout = false;

        if (issetReporticoSessionParam("xmlin"))
        //if ( array_key_exists("xmlin", $_SESSION[reporticoNamespace()]) )
        {
            $this->xmlinput = getReporticoSessionParam("xmlin");
            setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        if (issetReporticoSessionParam("sqlin"))
        //if ( array_key_exists("sqlin", $_SESSION[reporticoNamespace()]) )
        {
            $this->sqlinput = getReporticoSessionParam("sqlin");
        }

        if (array_key_exists("xmlin", $_REQUEST)) {
            setReporticoSessionParam("firstTimeIn", true);
            $this->xmlinput = $_REQUEST["xmlin"];

            unsetReporticoSessionParam("xmlintext");
            setReporticoSessionParam("xmlin", $this->xmlinput);
            setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        if ($this->initial_report) {
            $this->xmlinput = $this->initial_report;
            setReporticoSessionParam("xmlin", $this->xmlinput);
            setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        if ($this->initial_sql) {
            $this->sqlinput = false;
            if (!getReporticoSessionParam("sqlin")) {
                setReporticoSessionParam("sqlin", $this->initial_sql);
            }

            $this->sqlinput = getReporticoSessionParam("sqlin", $this->initial_sql);
            setReporticoSessionParam("xmlin", false);
            setReporticoSessionParam("xmlout", false);
        }

        if ($this->user_template == "_DEFAULT") {
            $this->user_template = false;
            setReporticoSessionParam('reportico_template', $this->user_template);
        } else if (!$this->user_template) {
            $this->user_template = sessionRequestItem('reportico_template', $this->user_template);
        }
        if (array_key_exists("partial_template", $_REQUEST)) {
            $this->user_template = $_REQUEST["partial_template"];
        }

        // Set template from request if specified
        if (issetReporticoSessionParam("template"))
        //if ( array_key_exists("template", $_SESSION[reporticoNamespace()]) )
        {
            $this->user_template = getReporticoSessionParam("template");
            setReporticoSessionParam("template", $this->user_template);
        }
        if (array_key_exists("template", $_REQUEST)) {
            $this->user_template = $_REQUEST["template"];
            setReporticoSessionParam("template", $this->user_template);
        }

        if ($this->xmlinput && !preg_match("/\.xml$/", $this->xmlinput)) {
            $this->xmlinput .= ".xml";
        }

        if (($this->xmlinput && $mode == "PREPARE" || $mode == "EXECUTE") && ($this->login_type == "NORMAL") && ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml")) {
            unsetReporticoSessionParam("xmlin");
            $this->xmlinput = "unknown.xml";
            $this->xmlin = "unknown.xml";
            $_REQUEST["xmlin"] = "unknown.xml";
            trigger_error("Can't find report", E_USER_NOTICE);
            return;
        }

        if ($this->xmlinput && !preg_match("/^[A-Za-z0-9]/", $this->xmlinput)) {
            unsetReporticoSessionParam("xmlin");
            $this->xmlinput = "unknown.xml";
            $this->xmlin = "unknown.xml";
            $_REQUEST["xmlin"] = "unknown.xml";
            trigger_error("Can't find report", E_USER_NOTICE);
            return;
        }

        // Now work out out file...
        if (!$this->xmloutfile) {
            $this->xmloutfile = $this->xmlinput;
        }

        if (issetReporticoSessionParam("xmlout"))
        //if ( array_key_exists("xmlout", $_SESSION[reporticoNamespace()]) )
        {
            $this->xmloutfile = getReporticoSessionParam("xmlout");
        }

        if (array_key_exists("xmlout", $_REQUEST) && (array_key_exists("submit_xxx_SAVE", $_REQUEST) || array_key_exists("submit_xxx_PREPARESAVE", $_REQUEST))) {
            $this->xmloutfile = $_REQUEST["xmlout"];
            setReporticoSessionParam("xmlout", $this->xmloutfile);
        }
        $this->xmlintext = false;
        if ($this->top_level_query && issetReporticoSessionParam("xmlintext")) {
            if (($this->xmlintext = getReporticoSessionParam("xmlintext"))) {
                $this->xmlinput = false;
            }
        }

        // Has new report been pressed ? If so clear any existing report
        // definitions
        if (array_key_exists("submit_maintain_NEW", $_REQUEST) ||
            array_key_exists("new_report", $_REQUEST)) {
            $this->xmlinput = false;
            $this->xmlintext = false;
            $this->xmloutfile = false;
            setReporticoSessionParam("xmlin", $this->xmlinput);
            setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        // apply default customized reportico actions if not using xml text in session
        $do_defaults = true;

        if ($this->sqlinput) {
            $this->importSQL($this->sqlinput);
        } else if ($this->xmlinput || $this->xmlintext) {
            if ($this->getExecuteMode() == "MAINTAIN") {
                $do_defaults = false;
            }
            //else if ( $this->xmlintext )
            //$do_defaults = false;

            $this->xmlin = new XmlReader($this, $this->xmlinput, $this->xmlintext);
            $this->xmlin->xml2query();
        } else {
            if ($this->getExecuteMode() == "MAINTAIN") {
                $do_defaults = false;
            }

            $this->xmlin = new XmlReader($this, false, "");
            $this->xmlin->xml2query();
        }

        // Custom query stuff loaded from reporticoDefaults.php. First look in project folder for
        // for project specific defaults
        if ($do_defaults) {
            $custom_functions = array();

            $old_error_handler = set_error_handler("Reportico\ErrorHandler", 0);
            if (@file_exists($this->projects_folder . "/" . ReporticoApp::getconfig("project") . "/reporticoDefaults.php")) {
                include_once $this->projects_folder . "/" . ReporticoApp::getconfig("project") . "/reporticoDefaults.php";
            } else if (@file_exists(__DIR__ . "/reporticoDefaults.php")) {
                include_once __DIR__ . "/reporticoDefaults.php";
            }

            if (function_exists("reporticoDefaults")) {
                reporticoDefaults($this);
            }

            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
        }

    }

    // -----------------------------------------------------------------------------
    // Function : get_panel
    // -----------------------------------------------------------------------------
    public function &getPanel($panel = false, $section = "ALL")
    {
        $txt = "";

        switch ($section) {
            case "PRE":
                $txt = $this->panels[$panel]->pre_text;
                break;
            case "POST":
                $txt = $this->panels[$panel]->post_text;
                break;
            default:
                $txt = $this->panels[$panel]->full_text;
                break;
        }
        return $txt;
    }

    // -----------------------------------------------------------------------------
    // Function : execute
    // -----------------------------------------------------------------------------
    public function importSQL($sql)
    {
        $this->setProjectEnvironment($this->initial_project, $this->projects_folder, $this->admin_projects_folder);
        $p = new SqlParser($sql);
        if ($p->parse(false)) {
            $p->importIntoQuery($this);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : execute
    // -----------------------------------------------------------------------------
    public function execute($mode = false, $draw = true)
    {
        global $g_system_errors;
        global $g_system_debug;
        global $g_code_area;
        global $g_code_source;
        global $g_debug_mode;

        if ($this->session_namespace) {
            ReporticoApp::set("session_namespace", $this->session_namespace);
        }

        if (ReporticoApp::get("session_namespace")) {
            ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
        }

        // If a session namespace doesnt exist create one
        if (!existsReporticoSession() || isset($_REQUEST['clear_session']) || $this->clear_reportico_session) {
            initializeReporticoNamespace(ReporticoApp::get("session_namespace_key"));
        }

        // Work out the mode (ADMIN, PREPARE, MENU, EXECUTE, MAINTAIN based on all parameters )
        if (!$mode) {
            $mode = $this->getExecuteMode();
        }

        $old_error_handler = set_error_handler("Reportico\ErrorHandler");
        set_exception_handler("Reportico\ExceptionHandler");

        // If new session, we need to use initial project, report etc, otherwise ignore them
        $this->handleInitialSettings();

        // load plugins
        $this->loadPlugins();

        // Fetch project config
        $this->setProjectEnvironment($this->initial_project, $this->projects_folder, $this->admin_projects_folder);

        registerSessionParam("external_user", $this->external_user);
        registerSessionParam("external_param1", $this->external_param1);
        registerSessionParam("external_param2", $this->external_param2);
        registerSessionParam("external_param3", $this->external_param3);

        $this->user_parameters = registerSessionParam("user_parameters", $this->user_parameters);
        $this->dropdown_menu = registerSessionParam("dropdown_menu", $this->dropdown_menu);
        $this->static_menu = registerSessionParam("static_menu", $this->static_menu);
        $this->charting_engine = registerSessionParam("charting_engine", $this->charting_engine);
        $this->charting_engine_html = registerSessionParam("charting_engine_html", $this->charting_engine_html);
        $this->output_template_parameters = registerSessionParam("output_template_parameters", $this->output_template_parameters);

        $this->dynamic_grids = registerSessionParam("dynamic_grids", $this->dynamic_grids);
        $this->dynamic_grids_sortable = registerSessionParam("dynamic_grids_sortable", $this->dynamic_grids_sortable);
        $this->dynamic_grids_searchable = registerSessionParam("dynamic_grids_searchable", $this->dynamic_grids_searchable);
        $this->dynamic_grids_paging = registerSessionParam("dynamic_grids_paging", $this->dynamic_grids_paging);
        $this->dynamic_grids_page_size = registerSessionParam("dynamic_grids_page_size", $this->dynamic_grids_page_size);

        // We are in AJAX mode if it is passed throuh
        if (isset($_REQUEST["reportico_ajax_called"])) {
            $this->reportico_ajax_called = $_REQUEST["reportico_ajax_called"];
        }

        //setReporticoSessionParam("reportico_ajax_called", $_REQUEST["reportico_ajax_called"] );

        // Store whether in framework
        setReporticoSessionParam("framework_parent", $this->framework_parent);

        // Set access mode to decide whether to allow user to access Design Mode, Menus, Criteria or just run a single report
        $this->access_mode = sessionItem("access_mode", $this->access_mode);
        if ($this->access_mode == "DEMO") {
            $this->allow_maintain = "DEMO";
        }

        // Convert input and out charsets into their PHP versions
        // for later iconv use
        $this->db_charset = dbCharsetToPhpCharset(ReporticoApp::getConfig("db_encoding", "UTF8"));
        $this->output_charset = outputCharsetToPhpCharset(ReporticoApp::getConfig("output_encoding", "UTF8"));

        // Ensure Smarty Template folder exists and is writeable
        $include_template_dir = $this->compiled_templates_folder;
        if (!(is_dir("templates_c"))) {
            findFileToInclude("templates_c", $include_template_dir, $include_template_dir);
        }

        if (!(is_dir($include_template_dir))) {
            echo "Unable to generate output. The <b>$include_template_dir</b> folder does not exist within the main reportico area. Please create this folder and ensure it has read, write and execute permissions and then retry.";
            die;
        }

        if (!swPathExecutable($include_template_dir)) {
            echo "Unable to generate output. The <b>$include_template_dir</b> folder does not have read, write and execute permissions. Please correct and retry.";
            die;
        }

        $g_debug_mode = getRequestItem("debug_mode", "0", $this->first_criteria_selection);

        if (!$mode) {
            $mode = $this->getExecuteMode();
        }

        // If the project is the ADMIN project then the MAin Menu will be the Admin Page
        if (ReporticoApp::getConfig("project") == "admin" && $mode == "MENU") {
            $mode = "ADMIN";
        }

        // If this is PREPARE mode then we want to identify whether user has entered prepare
        // screen for first time so we know whether to set defaults or not
        switch ($mode) {
            case "PREPARE":
                $this->reportProgress("Ready", "READY");
                $this->first_criteria_selection = true;
                // Must find ALternative to THIs for first time in testing!!!
                if (array_key_exists("targetFormat", $_REQUEST)) {
                    $this->first_criteria_selection = false;
                    setReporticoSessionParam("firstTimeIn", false);
                }

                if (!issetReporticoSessionParam("firstTimeIn")) {
                    setReporticoSessionParam("firstTimeIn", true);
                }

                // Default output to HTML in PREPARE mode first time in
                if (getReporticoSessionParam("firstTimeIn") && !isset($_REQUEST["targetFormat"])) {
                    $this->targetFormat = "HTML";
                    setReporticoSessionParam("targetFormat", "HTML");
                }

                // Default style to TABLE in PREPARE mode first time in
                //if ( getReporticoSessionParam("firstTimeIn") && !isset($_REQUEST["target_style"]))
                //{
                //$this->targetFormat = "TABLE";
                //setReporticoSessionParam("target_style","TABLE");
                //echo "set table ";
                //}

                break;

            case "EXECUTE":

                // If external page has supplied an initial output format then use it
                if ($this->initial_output_format) {
                    $_REQUEST["targetFormat"] = $this->initial_output_format;
                }

                // If printable HTML requested force output type to HTML
                if (getRequestItem("printable_html")) {
                    $_REQUEST["targetFormat"] = "HTML";
                }

                // Prompt user for report destination if target not already set - default to HTML if not set
                if (!array_key_exists("targetFormat", $_REQUEST) && $mode == "EXECUTE") {
                    $_REQUEST["targetFormat"] = "HTML";
                }

                $this->targetFormat = strtoupper($_REQUEST["targetFormat"]);

                if (array_key_exists("submit", $_REQUEST)) {
                    $this->first_criteria_selection = false;
                } else {
                    $this->first_criteria_selection = true;
                }

                if (getReporticoSessionParam("awaiting_initial_defaults")) {
                    setReporticoSessionParam("firstTimeIn", true);
                } else
                if (getReporticoSessionParam("firstTimeIn") && getRequestItem("refreshReport", false)) {
                    setReporticoSessionParam("firstTimeIn", true);
                } else {
                    setReporticoSessionParam("firstTimeIn", false);
                }

                break;

            case "MAINTAIN":
                $this->reportProgress("Ready", "READY");
                $this->first_criteria_selection = true;
                setReporticoSessionParam("firstTimeIn", true);
                break;

            default:
                //$this->report_progress("Ready", "READY" );
                $this->first_criteria_selection = true;
                setReporticoSessionParam("firstTimeIn", true);
                break;
        }

        // If xml file is used to generate the reportico_query, either by the xmlin session variable
        // or the xmlin request variable then process this before executing
        if ($mode == "EXECUTE") {
            $_REQUEST['execute_mode'] = "$mode";

            // If executing report then stored the REQUEST parameters unless this
            // is a refresh of the report in which case we want to keep the ones already there
            $runfromcriteriascreen = getRequestItem("user_criteria_entered", false);
            $refreshmode = getRequestItem("refreshReport", false);

            if (!getRequestItem("printable_html") && ($runfromcriteriascreen || (!issetReporticoSessionParam('latestRequest') || !getReporticoSessionParam('latestRequest')))) {
                setReporticoSessionParam('latestRequest', $_REQUEST);
            } else {
                if (!$runfromcriteriascreen && $refreshmode) {
                    $_REQUEST = getReporticoSessionParam('latestRequest');
                }
            }
        } else {
            if ($mode != "MODIFY" && issetReporticoSessionParam('latestRequest')) {
                if (getReporticoSessionParam('latestRequest')) {
                    $OLD_REQUEST = $_REQUEST;

                    // If a new report is being run dont bother trying to restore previous
                    // run crtieria
                    if (!getRequestItem("xmlin")) {
                        $_REQUEST = getReporticoSessionParam('latestRequest');
                    }

                    foreach ($OLD_REQUEST as $k => $v) {
                        if ($k == 'partial_template') {
                            $_REQUEST[$k] = $v;
                        }

                        if (preg_match("/^EXPAND_/", $k)) {
                            $_REQUEST[$k] = $v;
                        }

                    }
                    $_REQUEST['execute_mode'] = "$mode";
                }
            }
            setReporticoSessionParam('latestRequest', "");
        }

        // Derive URL call of the calling script so it can be recalled in form actions when not running in AJAX mode
        if (!$this->url_path_to_calling_script) {
            $this->url_path_to_calling_script = $_SERVER["SCRIPT_NAME"];
        }

        // Work out we are in AJAX mode
        $this->deriveAjaxOperation();

        switch ($mode) {
            case "CRITERIA":
                loadModeLanguagePack("languages", $this->output_charset);
                $this->initializePanels($mode);
                $this->handleXmlQueryInput($mode);
                $this->setRequestColumns();
                if (!isset($_REQUEST['reportico_criteria'])) {
                    echo "{ Success: false, Message: \"You must specify a criteria\" }";
                } else if (!$criteria = $this->getCriteriaByName($_REQUEST['reportico_criteria'])) {
                    echo "{ Success: false, Message: \"Criteria {$_REQUEST['reportico_criteria']} unknown in this report\" }";
                } else {
                    echo $criteria->executeCriteriaLookup();
                    echo $criteria->lookup_ajax();
                }
                die;

            case "MODIFY":
                require_once "DatabaseEngine.php";
                $this->initializePanels($mode);
                $engine = new DatabaseEngine($this->datasource->ado_connection->_connectionID);
                $status = $engine->performProjectModifications(ReporticoApp::getConfig("project"));
                if ($status["errstat"] != 0) {
                    header("HTTP/1.0 404 Not Found", true);
                }
                echo json_encode($status);
                die;

            case "ADMIN":
                $this->setRequestColumns();
                $txt = "";
                $this->handleXmlQueryInput($mode);
                $this->buildAdminScreen();
                $text = $this->panels["BODY"]->drawSmarty();
                $this->panels["MAIN"]->smarty->debugging = false;
                $this->panels["MAIN"]->smarty->assign('LANGUAGES', availableLanguages());
                $this->panels["MAIN"]->smarty->assign('CONTENT', $txt);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS', $this->dynamic_grids);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SORTABLE', $this->dynamic_grids_sortable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SEARCHABLE', $this->dynamic_grids_searchable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGING', $this->dynamic_grids_paging);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE', $this->dynamic_grids_page_size);

                restore_error_handler();

                // Some calling frameworks require output to be returned
                // for rendering inside web pages .. in this case
                // return_output_to_caller will be set to true
                $template = $this->getTemplatePath('admin.tpl');

                if ($this->return_output_to_caller) {
                    $txt = $this->panels["MAIN"]->smarty->fetch($template);
                    $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                    return $txt;
                } else {
                    $this->panels["MAIN"]->smarty->display($template);
                    $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                }
                break;

            case "MENU":
                $this->handleXmlQueryInput($mode);
                $this->setRequestColumns();
                $this->buildMenu();
                loadModeLanguagePack("languages", $this->output_charset);
                loadModeLanguagePack("menu", $this->output_charset);
                localiseTemplateStrings($this->panels["MAIN"]->smarty);

                $text = $this->panels["BODY"]->drawSmarty();
                $this->panels["MAIN"]->smarty->debugging = false;
                $this->panels["MAIN"]->smarty->assign('CONTENT', $text);
                $this->panels["MAIN"]->smarty->assign('LANGUAGES', availableLanguages());
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS', $this->dynamic_grids);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SORTABLE', $this->dynamic_grids_sortable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SEARCHABLE', $this->dynamic_grids_searchable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGING', $this->dynamic_grids_paging);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE', $this->dynamic_grids_page_size);

                restore_error_handler();
                // Some calling frameworks require output to be returned
                // for rendering inside web pages .. in this case
                // return_output_to_caller will be set to true
                $template = $this->getTemplatePath('menu.tpl');

                if ($this->return_output_to_caller) {
                    $txt = $this->panels["MAIN"]->smarty->fetch($template);
                    $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                    return $txt;
                } else {
                    $this->panels["MAIN"]->smarty->display($template);
                    $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                }
                break;

            case "PREPARE":
                loadModeLanguagePack("languages", $this->output_charset);
                $this->initializePanels($mode);
                $this->handleXmlQueryInput($mode);
                $this->setRequestColumns();

                if ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml" || $this->xmlinput == "generate_tutorial.xml") {
                    // If configuring project then use project language strings from admin project
                    // found in projects/admin/lang.php
                    loadProjectLanguagePack("admin", $this->output_charset);
                    $this->panels["MAIN"]->smarty->assign('SHOW_MINIMAINTAIN', false);
                    $this->panels["MAIN"]->smarty->assign('IS_ADMIN_SCREEN', true);
                }
                loadModeLanguagePack("prepare", $this->output_charset);
                localiseTemplateStrings($this->panels["MAIN"]->smarty);

                $text = $this->panels["BODY"]->drawSmarty();
                $this->panels["MAIN"]->smarty->debugging = false;
                $this->panels["MAIN"]->smarty->assign('CONTENT', $text);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS', $this->dynamic_grids);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SORTABLE', $this->dynamic_grids_sortable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SEARCHABLE', $this->dynamic_grids_searchable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGING', $this->dynamic_grids_paging);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE', $this->dynamic_grids_page_size);

                if ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml" || $this->xmlinput == "generate_tutorial.xml") {
                    $reportfile = "";
                } else {
                    $reportfile = preg_replace("/\.xml/", "", $this->xmloutfile);
                }

                $this->panels["MAIN"]->smarty->assign('XMLFILE', $reportfile);

                $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_prepare.tpl');
                restore_error_handler();

                // Some calling frameworks require output to be returned
                // for rendering inside web pages .. in this case
                // return_output_to_caller will be set to true
                $template = $this->getTemplatePath('prepare.tpl');

                if ($this->return_output_to_caller) {
                    $txt = $this->panels["MAIN"]->smarty->fetch($template);
                    $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                    return $txt;
                } else {
                    $this->panels["MAIN"]->smarty->display($template);
                    $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                }
                break;

            case "EXECUTE":
                loadModeLanguagePack("languages", $this->output_charset);
                $this->initializePanels($mode);
                $this->handleXmlQueryInput($mode);

                // Set Grid display options based on report and session defaults
                if ($this->attributes["gridDisplay"] != ".DEFAULT") {
                    $this->dynamic_grids = ($this->attributes["gridDisplay"] == "show");
                }

                if ($this->attributes["gridSortable"] != ".DEFAULT") {
                    $this->dynamic_grids_sortable = ($this->attributes["gridSortable"] == "yes");
                }

                if ($this->attributes["gridSearchable"] != ".DEFAULT") {
                    $this->dynamic_grids_searchable = ($this->attributes["gridSearchable"] == "yes");
                }

                if ($this->attributes["gridPageable"] != ".DEFAULT") {
                    $this->dynamic_grids_paging = ($this->attributes["gridPageable"] == "yes");
                }

                if ($this->attributes["gridPageSize"] != ".DEFAULT" && $this->attributes["gridPageSize"]) {
                    $this->dynamic_grids_page_size = $this->attributes["gridPageSize"];
                }

                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS', $this->dynamic_grids);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SORTABLE', $this->dynamic_grids_sortable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SEARCHABLE', $this->dynamic_grids_searchable);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGING', $this->dynamic_grids_paging);
                $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE', $this->dynamic_grids_page_size);

                $g_code_area = "Main Query";
                $this->buildQuery(false, "");
                $g_code_area = false;
                loadModeLanguagePack("execute", $this->output_charset);
                localiseTemplateStrings($this->panels["MAIN"]->smarty);
                $this->checkCriteriaValidity();

                if ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml") {
                    // If configuring project then use project language strings from admin project
                    // found in projects/admin/lang.php
                    loadProjectLanguagePack("admin", $this->output_charset);
                }

                if (!getReporticoSessionParam("loggedin", false)) {
                    $text = "you are not logged in ";
                } else
                if (!$this->return_to_caller) {
                    $text = $this->executeQuery(false);
                }

                if ($this->targetFormat == "SOAP") {
                    closeReporticoSession();
                    return;
                }

                // Situtations where we dont want to switch results page - no data found, debug mode, not logged in
                if ((count($g_system_errors) > 0 || $g_debug_mode || count($g_system_debug) > 0 || !getReporticoSessionParam("loggedin"))) {
                    // If errors and this is an ajax request return json ajax response for first message
                    $runfromcriteriascreen = getRequestItem("user_criteria_entered", false);
                    global $g_no_data;
                    //if ( $g_no_data && getRequestItem("new_reportico_window",  false ) && !$g_debug_mode && $this->targetFormat == "HTML" && $runfromcriteriascreen && $this->reportico_ajax_mode && count($g_system_errors) == 1 )
                    //
                    //{
                    //header("HTTP/1.0 404 Not Found", true);
                    //$response_array = array();
                    //$response_array["errno"] = $g_system_errors[0]["errno"];
                    //$response_array["errmsg"] = $g_system_errors[0]["errstr"];
                    //echo json_encode($response_array);
                    //die;
                    //}

                    header("HTTP/1.0 500 Not Found", true);
                    $this->initializePanels("PREPARE");
                    //$this->set_request_columns();

                    $this->panels["FORM"]->setVisibility(false);
                    $text = $this->panels["BODY"]->drawSmarty();

                    $this->panels["MAIN"]->smarty->debugging = false;
                    $title = swTranslate($this->deriveAttribute("ReportTitle", "Unknown"));
                    $this->panels["MAIN"]->smarty->assign('TITLE', $title);
                    $this->panels["MAIN"]->smarty->assign('CONTENT', $text);
                    if ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml" || $this->xmlinput == "generate_tutorial.xml") {
                        // If configuring project then use project language strings from admin project
                        // found in projects/admin/lang.php
                        loadProjectLanguagePack("admin", $this->output_charset);
                        $this->panels["MAIN"]->smarty->assign('SHOW_MINIMAINTAIN', false);
                        $this->panels["MAIN"]->smarty->assign('IS_ADMIN_SCREEN', true);
                    }
                    loadModeLanguagePack("languages", $this->output_charset, true);
                    loadModeLanguagePack("prepare", $this->output_charset);
                    localiseTemplateStrings($this->panels["MAIN"]->smarty);
                    $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_execute.tpl');
                    restore_error_handler();

                    // Some calling frameworks require output to be returned
                    // for rendering inside web pages .. in this case
                    // return_output_to_caller will be set to true
                    $template = $this->getTemplatePath('error.tpl');

                    if (false && $this->return_output_to_caller) {
                        $txt = $this->panels["MAIN"]->smarty->fetch($template);
                        $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                        return $txt;
                    } else {
                        $this->panels["MAIN"]->smarty->display($template);
                        $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                    }
                } else {
                    if ($this->targetFormat != "HTML") {
                        if ($draw) {
                            echo $text;
                        }

                    } else {
                        $title = swTranslate($this->deriveAttribute("ReportTitle", "Unknown"));

                        $pagestyle = $this->targets[0]->getStyleTags($this->output_reportbody_styles);

                        $this->panels["MAIN"]->smarty->assign('REPORT_PAGE_STYLE', $pagestyle);
                        $this->panels["MAIN"]->smarty->assign('TITLE', $title);
                        $this->panels["MAIN"]->smarty->assign('CONTENT', $text);

                        $this->panels["MAIN"]->smarty->assign('EMBEDDED_REPORT', $this->embedded_report);

                        // When printing in separate html window make sure we dont treat report as embedded
                        if (getRequestItem("new_reportico_window", false)) {
                            $this->panels["MAIN"]->smarty->assign('EMBEDDED_REPORT', false);
                        }

                        if ($this->email_recipients) {

                            $recipients = explode(',', $this->email_recipients);
                            foreach ($recipients as $rec) {
                                loadModeLanguagePack("languages", $this->output_charset, true);
                                loadModeLanguagePack("execute", $this->output_charset);
                                localiseTemplateStrings($this->panels["MAIN"]->smarty);
                                $template = $this->getTemplatePath('execute.tpl');
                                $mailtext = $this->panels["MAIN"]->smarty->fetch($template, null, null, false);
                                //$boundary = '-----=' . md5( uniqid ( rand() ) );
                                //$message = "Content-Type: text/html; name=\"my attachment\"\n";
                                //$message .= "Content-Transfer-Encoding: base64\n";
                                //$message .= "Content-Transfer-Encoding: quoted-printable\n";
                                //$message .= "Content-Disposition: attachment; filename=\"report.html\"\n\n";
                                $content_encode = chunk_split(base64_encode($mailtext));
                                $message = $mailtext . "\n";
                                //$message .= $boundary . "\n";
                                $headers = "From: \"Report Admin\"<me@here.com>\n";
                                $headers .= "MIME-Version: 1.0\n";
                                $headers .= "Content-Transfer-Encoding: base64\n";
                                //$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";
                                $headers = "Content-Type: text/html\n";
                                mail($rec, "$title", $message, $headers);
                            }
                        } else {
                            loadModeLanguagePack("languages", $this->output_charset, true);
                            loadModeLanguagePack("execute", $this->output_charset);
                            localiseTemplateStrings($this->panels["MAIN"]->smarty);
                            $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_execute.tpl');
                            restore_error_handler();

                            $template = $this->getTemplatePath('execute.tpl');

                            if ($this->return_output_to_caller) {
                                $txt = $this->panels["MAIN"]->smarty->fetch($template);
                                $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                                return $txt;
                            } else {
                                $this->panels["MAIN"]->smarty->display($template);
                                $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                            }

                        }
                    }
                }
                break;

            case "MAINTAIN":
                // Avoid url manipulation by only allowing maintain mode in design or demo mode
                $this->handleXmlQueryInput($mode);
                if ($this->top_level_query) {
                    $this->initializePanels($mode);
                    if (!($this->login_type == "DESIGN" || $this->access_mode == "DEMO")) {
                        break;
                    }

                    loadModeLanguagePack("maintain", $this->output_charset);
                    localiseTemplateStrings($this->panels["MAIN"]->smarty);
                    $this->xmlin->handleUserEntry();
                    setReporticoSessionParam("xmlintext", $this->xmlintext);

                    $text = $this->panels["BODY"]->drawSmarty();
                    $this->panels["MAIN"]->smarty->assign('PARTIALMAINTAIN', getRequestItem("partialMaintain", false));
                    $this->panels["MAIN"]->smarty->assign('CONTENT', $text);
                    $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS', $this->dynamic_grids);
                    $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SORTABLE', $this->dynamic_grids_sortable);
                    $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_SEARCHABLE', $this->dynamic_grids_searchable);
                    $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGING', $this->dynamic_grids_paging);
                    $this->panels["MAIN"]->smarty->assign('REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE', $this->dynamic_grids_page_size);
                    $template = $this->getTemplatePath('maintain.tpl');
                    $this->panels["MAIN"]->smarty->display($template);
                } else {
                    $this->premaintainQuery();
                }
                break;

            case "XMLOUT":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->prepareXmlData();

                if (array_key_exists("xmlout", $_REQUEST)) {
                    $this->xmlout->writeFile($_REQUEST["xmlout"]);
                } else {
                    $this->xmlout->write();
                }

                break;

            case "XMLSHOW":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->prepareXmlData();
                $this->xmlout->write();
                break;

            case "WSDLSHOW":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->prepareWsdlData();
                break;

            case "SOAPSAVE":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->generateWebService($this->xmloutfile);
                break;
        }

        $this->handledInitialSettings();

        closeReporticoSession();
    }

    // -----------------------------------------------------------------------------
    // Function : build_admin_screen()
    // -----------------------------------------------------------------------------
    public function buildAdminScreen()
    {

        $p = new DesignPanel($this, "ADMIN");
        $this->initializePanels("ADMIN");
        $this->setAttribute("ReportTitle", ReporticoApp::get('menu_title'));
        loadModeLanguagePack("languages", $this->output_charset);
        loadModeLanguagePack("admin", $this->output_charset);
        localiseTemplateStrings($this->panels["MAIN"]->smarty);

        global $g_projpath;

        if (ReporticoApp::getConfig("project") != "admin") {
            return;
        }

        if (ReporticoApp::get("static_menu") && is_array(ReporticoApp::get("static_menu"))) {
            $ct = 0;
            foreach (ReporticoApp::get("static_menu") as $menuitem) {
                if ($menuitem["title"] == "<AUTO>") {
                    // Generate Menu from XML files
                    if (is_dir($g_projpath)) {
                        if ($dh = opendir($g_projpath)) {
                            while (($file = readdir($dh)) !== false) {
                                $mtch = "/" . $menuitem["report"] . "/";
                                if (preg_match($mtch, $file)) {
                                    $repxml = new XmlReader($this, $file, false, "ReportTitle");
                                    $this->panels["MENU"]->setMenuItem($file, $repxml->search_response);
                                }
                            }
                            closedir($dh);
                        }
                    }
                } else {
                    $this->panels["MENU"]->setMenuItem($menuitem["report"], templateXlate($menuitem["title"]));
                }
                $ct++;
            }

            if ($ct == 0) {
                handleError("No Menu Items Available - Check Language - " . ReporticoApp::get("language"));
            }

            // Generate list of projects to choose from by finding all folders above the
            // current project area (i.e. the projects folder) and looking for any folder
            // that contains a config.php file (which means it proably is a project)
            $projpath = $this->projects_folder;
            if (!is_dir($projpath)) {
                findFileToInclude($projpath, $projpath);
            }

            if (is_dir($projpath)) {
                $ct = 0;
                if ($dh = opendir($projpath)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == ".") {
                            continue;
                        }

                        if (is_dir($projpath . "/" . $file)) {
                            if (is_file($projpath . "/" . $file . "/config.php")) {
                                //$repxml = new XmlReader($this, $file, false, "ReportTitle");
                                $this->panels["ADMIN"]->setProjectItem($file, $file);
                            }
                        }

                    }
                    closedir($dh);
                }
            }

        }
    }

    // -----------------------------------------------------------------------------
    // Function : build_menu()
    // -----------------------------------------------------------------------------
    public function buildMenu()
    {

        global $g_projpath;

        if (!$this->static_menu && !is_array($this->static_menu)) {
            $this->static_menu = ReporticoApp::get("static_menu");
        }

        // In admin mode static_menu shows all reports
        if (issetReporticoSessionParam('admin_password')) {
            // .. unless an admin menu has been specified
            if (ReporticoApp::get("admin_menu")) {
                $this->static_menu = ReporticoApp::get("admin_menu");
            } else {
                $this->static_menu = array(array("report" => ".*\.xml", "title" => "<AUTO>"));
            }

        }

        $p = new DesignPanel($this, "MENU");
        $this->initializePanels("MENU");
        $this->setAttribute("ReportTitle", ReporticoApp::getConfig("project_title", "Report Suite"));

        if ($this->static_menu && is_array($this->static_menu)) {
            $ct = 0;
            foreach ($this->static_menu as $menuitem) {
                if ($menuitem["title"] == "<AUTO>") {
                    // Generate Menu from XML files
                    if (is_dir($g_projpath)) {
                        if ($dh = opendir($g_projpath)) {
                            while (($file = readdir($dh)) !== false) {
                                $mtch = "/^" . $menuitem["report"] . "/";
                                if (preg_match($mtch, $file)) {
                                    $repxml = new XmlReader($this, $file, false, "ReportTitle");
                                    $this->panels["MENU"]->setMenuItem($file, swTranslate($repxml->search_response));
                                }
                            }
                            closedir($dh);
                        }
                    }
                } else {
                    $this->panels["MENU"]->setMenuItem($menuitem["report"], swTranslate($menuitem["title"]));
                }

                $ct++;
            }

            if ($ct == 0) {
                handleError("No Menu Items Available - Check Language - " . ReporticoApp::get("language"));
            }

        }
    }

    // -----------------------------------------------------------------------------
    // Function : premaintain_query
    // -----------------------------------------------------------------------------
    public function premaintainQuery()
    {
        foreach ($this->pre_sql as $sql) {
            $nsql = Assignment::reporticoLookupStringToPhp($sql);
            $recordSet = false;
            $errorCode = false;
            $errorMessage = false;
            try {
                $recordSet = $conn->Execute($nsql);
            } catch (PDOException $ex) {
                $errorCode = $ex->getCode();
                $errorMessage = $ex->getMessage();
            }
            if (!$recordSet) {
                if ($errorMessage) {
                    handleError("Query Failed<BR><BR>" . $nsql . "<br><br>" .
                        $errorMessage);
                } else {
                    handleError("Query Failed<BR><BR>" . $nsql . "<br><br>" .
                        "Status " . $conn->ErrorNo() . " - " .
                        $conn->ErrorMsg());
                }

            }
        }

        $this->fetchColumnAttributes();

        // Run query for each target. Currently having more than
        // one target means first target is array which becomes source
        // for second target
        //for ($i = 0; $i < count($this->targets); $i++ )
        for ($i = 0; $i < 1; $i++) {
            $target = &$this->targets[$i];

            $target->setQuery($this);
            $target->setColumns($this->columns);
            $target->start();
        }
    }

    // -----------------------------------------------------------------------------
    // Function : executeQuery
    // -----------------------------------------------------------------------------
    public function executeQuery($in_criteria_name)
    {
        global $g_code_area;
        global $g_code_source;
        global $g_error_status;

        $text = "";

        $this->fetchColumnAttributes();

        // Run query for each target. Currently having more than
        // one target means first target is array which becomes source
        // for second target
        //for ($i = 0; $i < count($this->targets); $i++ )
        for ($_counter = 0; $_counter < 1; $_counter++) {
            $target = &$this->targets[$_counter];
            $target->setQuery($this);
            $target->setColumns($this->columns);
            $target->start();
            //}

            // Reset all old column values to junk
            foreach ($this->columns as $k => $col) {
                $this->columns[$k]->old_column_value = "";
            }

            if ($_counter > 0) {
                // Execute query 2
                $this->assignment = array();
                $ds = new ReporticoDataSource();
                $this->setDatasource($ds);

                $ds->setDatabase($this->targets[0]->results);
                $ds->connect();

                foreach ($this->columns as $k => $col) {
                    $this->columns[$k]->in_select = true;
                }
            }

            /* Performing SQL query */
            $ds = &$this->datasource;
            $conn = &$this->datasource->ado_connection;

            $this->debug($this->query_statement);
            //$conn->debug = true;

            foreach ($this->pre_sql as $sql) {
                $g_code_area = "Custom User SQLs";
                $nsql = Assignment::reporticoMetaSqlCriteria($this, $sql, true);
                handleDebug("Pre-SQL" . $nsql, REPORTICO_DEBUG_LOW);
                $recordSet = false;
                $errorCode = false;
                $errorMessage = false;
                try {
                    $recordSet = $conn->Execute($nsql);
                } catch (PDOException $ex) {
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                }
                if (!$recordSet) {
                    if ($errorMessage) {
                        handleError("Pre-Query Failed<BR><BR>" . $nsql . "<br><br>" .
                            $errorMessage);
                    } else {
                        handleError("Pre-Query Failed<BR><BR>" . $nsql . "<br><br>" .
                            "Status " . $conn->ErrorNo() . " - " .
                            $conn->ErrorMsg());
                    }

                }
                $g_code_area = "";
            }

            if (!$in_criteria_name) {
                // Execute Any Pre Execute Code, if not specified then
                // attempt to pick up code automatically from a file "projects/project/report.xml.php"
                $code = $this->getAttribute("PreExecuteCode");
                if (!$code || $code == "NONE" || $code == "XX") {
                    if (preg_match("/.xml$/", getReporticoSessionParam("xmlin"))) {
                        $source_path = findBestLocationInIncludePath($this->projects_folder . "/" . ReporticoApp::getConfig("project") . "/" . getReporticoSessionParam("xmlin") . ".php");
                    } else {
                        $source_path = findBestLocationInIncludePath($this->projects_folder . "/" . ReporticoApp::getConfig("project") . "/" . getReporticoSessionParam("xmlin") . ".xml.php");
                    }

                    if (is_file($source_path)) {
                        $code = file_get_contents($source_path);
                    } else {
                        $code = false;
                    }

                }
                if ($code) {
                    $g_code_area = "";
                    $code = "\$lk =& \$this->lookup_queries;" . $code;
                    $code = "\$ds =& \$this->datasource->ado_connection;" . $code;
                    $code = "\$_criteria =& \$this->lookup_queries;" . $code;
                    $code = "\$_pdo =& \$_connection->_connectionID;" . $code;
                    $code = "if ( \$_connection )" . $code;
                    $code = "\$_pdo = false;" . $code;
                    $code = "\$_connection =& \$this->datasource->ado_connection;" . $code;

                    // set to the user defined error handler
                    global $g_eval_code;
                    $g_eval_code = $code;
                    // If parse error in eval code then use output buffering contents to show user the error
                    $ob_level = ob_get_level();
                    if ($ob_level > 0) {
                        ob_start();
                    }

                    eval($code);
                    $eval_output = ob_get_contents();
                    if ($ob_level > 0) {
                        ob_end_clean();
                    }

                    // Check for parse error
                    if (preg_match("/.*Parse error.*on line <b>(.*)<.b>/", $eval_output, $parseerrors)) {
                        // There is a parse error in the evaluated code .. find the relevant line
                        $errtext = "Parse Error in custom report code: <br><hr>$eval_output<PRE>";
                        foreach (preg_split("/(\r?\n)/", $code) as $lno => $line) {
                            // do stuff with $line
                            if ($lno > $parseerrors[1] - 3 && $lno < $parseerrors[1] + 3) {
                                if ($lno == $parseerrors[1]) {
                                    $errtext .= ">>>  ";
                                } else {
                                    $errtext .= "     ";
                                }

                                $errtext .= $line;
                                $errtext .= "\n";
                            }
                        }
                        $errtext .= "</PRE>";
                        trigger_error($errtext, E_USER_ERROR);

                    } else {
                        echo $eval_output;
                    }
                    $g_code_area = "";
                    $g_code_source = "";
                }
            }
            $recordSet = false;

            if ($in_criteria_name) {
                $g_code_area = "Criteria " . $in_criteria_name;
            } else {
                $g_code_area = "Main Report Query";
            }

            // User may have flagged returning before SQL performed
            if (ReporticoApp::get("no_sql")) {
                return;
            }

            $recordSet = false;
            $errorCode = false;
            $errorMessage = false;

            // If the source is an array then dont try to run SQL
            if (get_class($conn) == "DataSourceArray") {
                $recordSet = $conn;
            } else {
                try {
                    if (!$g_error_status && $conn != false) {
                        $recordSet = $conn->Execute($this->query_statement);
                    }

                } catch (PDOException $ex) {
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                    $g_error_status = 1;
                }
            }
            if ($conn && !$recordSet) {
                if ($errorMessage) {
                    handleError("Query Failed<BR><BR>" . $this->query_statement . "<br><br>" .
                        $errorMessage);
                } else {
                    handleError("Query Failed<BR><BR>" . $this->query_statement . "<br><br>" .
                        "Status " . $conn->ErrorNo() . " - " .
                        $conn->ErrorMsg());
                }

            }

            if ($conn != false) {
                handleDebug($this->query_statement, REPORTICO_DEBUG_LOW);
            }

            // Begin Target Output
            //handleError("set");
            if (!$recordSet || $g_error_status) {
                //handleError("stop");
                return;
            }

            // Main Query Result Fetching
            $this->query_count = 0;
            while (!$recordSet->EOF) {

                $line = $recordSet->FetchRow();
                if ($line == null) {
                    $recordSet->EOF = true;
                    continue;
                }
                $this->query_count++;

                $g_code_area = "Build Column";
                $this->buildColumnResults($line);

                $g_code_area = "Assignment";

                if ($_counter < 1) {
                    $target->setDefaultStyles();
                    $this->charsetEncodeDbToOutput();
                    $this->assign();
                }
                $g_code_source = false;

                // Skip line if required
                if ($this->output_skipline) {
                    $this->query_count--;
                    $this->output_skipline = false;
                    continue;
                }

                $g_code_area = "Line Output";
                $target->eachLine($line);

                $g_code_area = "Store Output";
                $this->storeColumnResults();
                if ($recordSet->EOF) {
                    break;
                }

            }
            $g_code_area = "";

            global $g_no_data;
            $g_no_data = false;

            if ($this->query_count == 0 && !$in_criteria_name && (!$this->access_mode || $this->access_mode != "REPORTOUTPUT")) {
                $g_no_data = true;
                handleError(templateXlate("NO_DATA_FOUND"), E_USER_WARNING);
                return;
            }

            // Complete Target Output
            //for ($_counter = 0; $_counter < count($this->targets); $_counter++ )
            //{
            //$target =& $this->targets[$_counter];
            $target->finish();
            $text = &$target->text;

            /* Free resultset */
            $recordSet->Close();

        }
        return $text;

    }

    // -----------------------------------------------------------------------------
    // Function : get_column
    // -----------------------------------------------------------------------------
    public function &getColumn($query_name)
    {
        $retval = null;
        foreach ($this->columns as $col) {
            if ($col->query_name == $query_name) {
                $retval = &$col;
                break;
            }
        }
        return $retval;
    }

    // -----------------------------------------------------------------------------
    // Function : fetch_column_attributes
    // -----------------------------------------------------------------------------
    public function fetchColumnAttributes()
    {
        $conn = $this->datasource->ado_connection;
        //$a = new Reportico($this->datasource);
        //$old_database = $a->database;

        $datadict = false;
        reset($this->columns);
        $lasttab = "";
        while ($d = key($this->columns)) {
            $value = &$this->columns[$d];

            if (array_key_exists($value->query_name, $this->clone_columns)) {
                $value->column_type =
                $this->clone_columns[$value->query_name][0];
                $value->column_length =
                $this->clone_columns[$value->query_name][1];

            } else if ($value->table_name) {
                if ($lasttab != $value->table_name) {
                    $datadict = $this->datasource->ado_connection->MetaColumns($value->table_name);
                    if (!$datadict) {
                        // echo "Data Dictionary Attack Failed Table $value->table_name\n";
                        // echo "Error ".$this->datasource->ado_connection->ErrorMsg()."<br>";
                        //die;
                    }
                }
                foreach ($datadict as $k => $v) {

                    if (strtoupper(trim($k)) == strtoupper($value->column_name)) {
                        //$coldets = $datadict[strtoupper($value->column_name)];
                        $coldets = $datadict[$k];
                        $value->column_type =
                        ReporticoDataSource::mapColumnType(
                            $this->datasource->driver,
                            $datadict[$k]->type);

                        if (strtoupper($value->column_type) == "INTEGER") {
                            $value->column_length = 0;
                        } else if (strtoupper($value->column_type) == "SMALLINT") {
                            $value->column_length = 0;
                        } else {
                            $value->column_length = (int) $datadict[$k]->max_length;
                        }

                        break;
                    }
                }
            }
            $lasttab = $value->table_name;
            next($this->columns);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : dd_assignment
    // -----------------------------------------------------------------------------
    public function addAssignment
    (
        $query_name,
        $expression,
        $criteria,
        $atstart = false
    ) {
        //print("Added assign $query_name, $expression, $criteria<BR>");
        if ($atstart) {
            array_unshift($this->assignment, new Assignment
                (
                    $query_name,
                    $expression,
                    $criteria
                )
            );
        } else {
            $this->assignment[] = new Assignment
            (
                $query_name,
                $expression,
                $criteria
            );
        }

    }

    // -----------------------------------------------------------------------------
    // Function : charset_encode_db_to_output
    // -----------------------------------------------------------------------------
    public function charsetEncodeDbToOutput()
    {
        if ($this->db_charset && $this->output_charset) {
            if ($this->db_charset != $this->output_charset) {
                foreach ($this->columns as $col) {
                    $col->column_value = iconv($this->db_charset, $this->output_charset, $col->column_value);
                }
            }
        }

    }

    // -----------------------------------------------------------------------------
    // Function : assign
    // -----------------------------------------------------------------------------
    public function assign()
    {
        global $g_debug_mode;
        global $g_code_area;
        global $g_code_source;

        // Clear any styles or instructions left over from previous rows
        foreach ($this->columns as $col) {

            $col->output_cell_styles = false;
            $col->output_images = false;
            $col->output_hyperlinks = false;
        }

        // Perform assignments
        foreach ($this->assignment as $assign) {
            $col = getQueryColumn($assign->query_name, $this->columns);
            if (!$col) {
                continue;
            }
            $g_code_area = "Assignment";
            $g_code_source = "<BR>In Assignment if " . $assign->criteria . "<BR>";
            $g_code_source = "<BR>In Assignment " . $assign->query_name . "=" . $assign->expression;
            if ($this->test($assign->criteria)) {
                if ($assign->non_assignment_operation) {
                    $a = $assign->expression . ';';
                } else {
                    $a = '$col->column_value = ' . $assign->expression . ';';
                }

                $r = eval($a);

                if ( /*REPORTICO_DEBUG ||*/$g_debug_mode) {
                    handleDebug("Assignment " . $assign->query_name . " = " . $assign->expression .
                        " => " . $col->column_value, REPORTICO_DEBUG_HIGH);
                }

            }

        }
    }

    public function getQueryColumnValue($name, &$arr)
    {
        $ret = "NONE";
        foreach ($arr as $val) {
            if ($val->query_name == $name) {
                return $val->column_value;
            }
        }

        // Extract criteria item
        if (substr($name, 0, 1) == "?" || substr($name, 0, 1) == "=") {
            $field = substr($name, 1);
            $bits = explode(",", $field);
            if (isset($this->lookup_queries[$bits[0]])) {
                if (!isset($bits[1])) {
                    $bits[1] = "VALUE";
                }

                if (!isset($bits[2])) {
                    $bits[2] = false;
                }

                if ($bits[1] != "RANGE1" && $bits[1] != "RANGE2" && $bits[1] != "FULL" && $bits[1] != "VALUE") {
                    $bits[1] = "VALUE";
                }

                $x = $this->lookup_queries[$bits[0]]->getCriteriaValue($bits[1], $bits[2]);
                return $x;
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : test
    // -----------------------------------------------------------------------------
    public function test($criteria)
    {

        $test_result = false;

        if (!$criteria) {
            return (true);
        }

        $test_string = 'if ( ' . $criteria . ' ) $test_result = true;';
        eval($test_string);
        return $test_result;
    }

    // -----------------------------------------------------------------------------
    // Function : changed
    // -----------------------------------------------------------------------------
    public function changed($query_name)
    {

        $result = false;

        if ($query_name == "REPORT_BODY") {
            return false;
        }

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes a changed assignment involving a column ($query_name) that does not exist within the report. Perhaps a group needs to be deleted");
            return $result;
        }

        if ($col->column_value
            != $col->old_column_value) {
            $result = true;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : reset
    // -----------------------------------------------------------------------------
    public function reset($query_name)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a column ($query_name) that does not exist within the report. Perhaps a group needs to be deleted");
            return 0;
        }
        $col->reset_flag = true;
        $col->column_value = 0;

        return 0;
    }

    // -----------------------------------------------------------------------------
    // Function : groupcount
    // -----------------------------------------------------------------------------
    public function groupcount($groupname, $result_name)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a column ($query_name) that does not exist within the report. Perhaps a group needs to be deleted");
            return 0;
        }
        $res = getQueryColumn($result_name, $this->columns);

        if ($this->changed($groupname)) {
            $this->reset($result_name);
        }

        if ($res->old_column_value && !$res->reset_flag) {
            $result = $res->old_column_value + $col->column_value;
        } else {
            $result = $col->column_value;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : skipline
    // Causes current line output to be skipped/not outputted
    // -----------------------------------------------------------------------------
    public function skipline()
    {
        $this->output_skipline = true;
    }

    // -----------------------------------------------------------------------------
    // Function : embed_image
    // Generates a link object against a column
    // -----------------------------------------------------------------------------
    public function embedImage($column_assignee, $image, $width = false, $height = false)
    {
        getQueryColumn($column_assignee, $this->columns)->output_images =
        array("image" => $image, "width" => $width, "height" => $height);
    }

    // -----------------------------------------------------------------------------
    // Function : create_hyperlink
    // Generates a link object against a column
    // -----------------------------------------------------------------------------
    public function embedHyperlink($column_assignee, $label, $url, $open_in_new = true, $is_drilldown = false)
    {
        getQueryColumn($column_assignee, $this->columns)->output_hyperlinks =
        array("label" => $label, "url" => $url, "open_in_new" => $open_in_new, "is_drilldown" => $is_drilldown);
    }

    // -----------------------------------------------------------------------------
    // Function : applyStyle
    // Sets up style instructions against an output row, cell or page
    // For example allows a cell to appear in a particular color
    // or with specified margins, or allows a row to have a border above etc
    // Styles relate to CSS and are transferred where supported through to PDF
    // -----------------------------------------------------------------------------
    public function applyStyle($column_assignee, $item_type, $style_type, $style_value)
    {
        if ($item_type == "ALLCELLS") {
            $this->output_allcell_styles[$style_type] = $style_value;
        }

        if ($item_type == "CRITERIA") {
            $this->output_criteria_styles[$style_type] = $style_value;
        }

        if ($item_type == "ROW") {
            $this->output_row_styles[$style_type] = $style_value;
        }

        if ($item_type == "CELL") {
            getQueryColumn($column_assignee, $this->columns)->output_cell_styles[$style_type] = $style_value;
        }

        if ($item_type == "PAGE") {
            $this->output_page_styles[$style_type] = $style_value;
        }

        if ($item_type == "BODY") {
            $this->output_reportbody_styles[$style_type] = $style_value;
        }

        if ($item_type == "COLUMNHEADERS") {
            $this->output_header_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPHEADER") {
            $this->output_group_header_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPHEADERLABEL") {
            $this->output_group_header_label_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPHEADERVALUE") {
            $this->output_group_header_value_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPTRAILER") {
            $this->output_group_trailer_styles[$style_type] = $style_value;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : lineno
    // -----------------------------------------------------------------------------
    public function lineno($group_name = false)
    {
        $result = 0;
        if ($group_name) {
            if (!array_key_exists($group_name, $this->groupvals)) {
                $this->groupvals[$group_name] =
                array("lineno" => 0);
            }

            if ($this->changed($group_name)) {
                $this->groupvals[$group_name]["lineno"] = 1;
            } else {
                $this->groupvals[$group_name]["lineno"]++;
            }

            $result = $this->groupvals[$group_name]["lineno"];
        } else {
            $result = $this->query_count;
        }

        return ($result);
    }

    // -----------------------------------------------------------------------------
    // Function : sum
    // -----------------------------------------------------------------------------
    public function sum($query_name, $group_name = false)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an sum assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }
        $result = str_replace(",", "", $col->column_value);

        if ($col->old_column_value && !$col->reset_flag) {
            $result =
            $col->old_column_value +
            str_replace(",", "", $col->column_value);
        }
        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                array("average" => 0,
                    "sum" => "0",
                    "avgct" => 0,
                    "avgsum" => 0,
                    "min" => 0,
                    "max" => 0);
            }

            if ($this->changed($group_name)) {
                $col->groupvals[$group_name]["sum"] = str_replace(",", "", $col->column_value);
            } else {
                $col->groupvals[$group_name]["sum"] += str_replace(",", "", $col->column_value);
            }

            $result = $col->groupvals[$group_name]["sum"];
        } else {
            if ($col->reset_flag || !$col->sum) {
                $col->sum = str_replace(",", "", $col->column_value);
            } else {
                $col->sum += str_replace(",", "", $col->column_value);
            }

            $result = $col->sum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : sum
    // -----------------------------------------------------------------------------
    public function solosum($query_name, $group_name = false)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                array("average" => 0,
                    "sum" => "0",
                    "avgct" => 0,
                    "avgsum" => 0,
                    "min" => 0,
                    "max" => 0);
            }

            if ($this->changed($group_name)) {
                $col->groupvals[$group_name]["sum"] = $col->column_value;
            } else {
                $col->groupvals[$group_name]["sum"] += $col->column_value;
            }

            $result = $col->groupvals[$group_name]["sum"];
        } else {
            if ($col->reset_flag || !$col->sum) {
                $col->sum = $col->column_value;
            } else {
                $col->sum += $col->column_value;
            }

            $result = $col->sum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : avg
    // -----------------------------------------------------------------------------
    public function avg($query_name, $group_name = false)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                array("average" => 0,
                    "sum" => "0",
                    "avgct" => 0,
                    "avgsum" => 0,
                    "min" => 0,
                    "max" => 0);
            }

            $grpval = &$col->groupvals[$group_name];
            if ($this->changed($group_name)) {
                $grpval["avgct"] = 1;
                $grpval["average"] = $col->column_value;
                $grpval["avgsum"] = $col->column_value;
            } else {
                $grpval["avgct"]++;
                $grpval["avgsum"] += $col->column_value;
                $grpval["average"] =
                $grpval["avgsum"] /
                $grpval["avgct"];
            }
            $result = $grpval["average"];
        } else {
            if ($col->reset_flag || !$col->average) {
                $col->avgct = 1;
                $col->average = $col->column_value;
                $col->avgsum = $col->column_value;
            } else {
                $col->avgct++;
                $col->avgsum += $col->column_value;
                $col->average = $col->avgsum / $col->avgct;
            }
            $result = $col->average;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : max
    // -----------------------------------------------------------------------------
    public function max($query_name, $group_name = false)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                array("average" => 0,
                    "sum" => "0",
                    "avgct" => 0,
                    "avgsum" => 0,
                    "min" => 0,
                    "max" => 0);
            }

            $grpval = &$col->groupvals[$group_name];
            if ($this->changed($group_name)) {
                $grpval["max"] = $col->column_value;
            } else {
                if ($grpval["max"] < $col->column_value) {
                    $grpval["max"] = $col->column_value;
                }

            }
            $result = $grpval["max"];
        } else {
            if ($col->reset_flag || !$col->maximum) {
                $col->maximum = $col->column_value;
            } else
            if ($col->maximum < $col->column_value) {
                $col->maximum = $col->column_value;
            }

            $result = $col->maximum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : min
    // -----------------------------------------------------------------------------
    public function min($query_name, $group_name = false)
    {

        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                array("average" => 0,
                    "sum" => "0",
                    "avgct" => 0,
                    "avgsum" => 0,
                    "min" => 0,
                    "max" => 0);
            }

            $grpval = &$col->groupvals[$group_name];
            if ($this->changed($group_name)) {
                $grpval["min"] = $col->column_value;
            } else {
                if ($grpval["min"] > $col->column_value) {
                    $grpval["min"] = $col->column_value;
                }

            }
            $result = $grpval["min"];
        } else {
            if ($col->reset_flag || !$col->minimum) {
                $col->minimum = $col->column_value;
            } else
            if ($col->minimum > $col->column_value) {
                $col->minimum = $col->column_value;
            }

            $result = $col->minimum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : old
    // -----------------------------------------------------------------------------
    public function old($query_name)
    {
        $col = getQueryColumn($query_name, $this->columns);
        if (!$col) {
            handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        if (!$col->reset_flag) {
            return $col->old_column_value;
        } else {
            return false;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : imagequery
    // -----------------------------------------------------------------------------
    public function imagequery($imagesql, $width = 200)
    {

        $conn = &$this->datasource;

        //$imagesql = str_replace($imagesql, '"', "'");
        $imagesql = preg_replace("/'/", "\"", $imagesql);
        //$params="driver=".$conn->driver."&dbname=".$conn->database."&hostname=".$conn->host_name;
        $params = "dummy=xxx";

        // Link to db image depaends on the framework used. For straight reportico, its a call to the imageget.php
        // file, for Joomla it must go through the Joomla index file
        $imagegetpath = dirname($this->url_path_to_reportico_runner) . "/" . findBestUrlInIncludePath("imageget.php");
        if ($this->framework_parent) {
            $imagegetpath = "";
            if ($this->reportico_ajax_mode == "2") {
                $imagegetpath = preg_replace("/ajax/", "dbimage", $this->reportico_ajax_script_url);
            }

        }

        $forward_url_params = sessionRequestItem('forward_url_get_parameters_dbimage');
        if (!$forward_url_params) {
            $forward_url_params = sessionRequestItem('forward_url_get_parameters', $this->forward_url_get_parameters);
        }

        if ($forward_url_params) {
            $params .= "&" . $forward_url_params;
        }

        $params .= "&reporticoSessionName=" . reporticoSessionName();

        $result = '<img width="' . $width . '" src=\'' . $imagegetpath . '?' . $params . '&reportico_call_mode=dbimage&imagesql=' . $imagesql . '\'>';

        return $result;
    }

    /**
     * Function generate_dropdown_menu
     *
     * Writes new admin password to the admin config.php
     */
    public function generateDropdownMenu(&$menu)
    {
        foreach ($menu as $k => $v) {
            $project = $v["project"];
            $initproject = $v["project"];
            $projtitle = "<AUTO>";
            if (isset($v["title"])) {
                $projtitle = $v["title"];
            }

            $menu[$k]["title"] = swTranslate($projtitle);
            foreach ($v["items"] as $k1 => $menuitem) {
                if (!isset($menuitem["reportname"]) || $menuitem["reportname"] == "<AUTO>") {
                    // Generate Menu from XML files
                    if (is_dir($this->projects_folder)) {
                        $proj_parent = $this->projects_folder;
                    } else {
                        $proj_parent = findBestLocationInIncludePath($this->projects_folder);
                    }

                    $project = $initproject;
                    if (isset($menuitem["project"])) {
                        $project = $menuitem["project"];
                    }

                    $filename = $proj_parent . "/" . $project . "/" . $menuitem["reportfile"];
                    if (!preg_match("/\.xml/", $filename)) {
                        $filename .= ".xml";
                    }

                    if (is_file($filename)) {
                        $query = false;
                        $repxml = new XmlReader($query, $filename, false, "ReportTitle");
                        $menu[$k]["items"][$k1]["reportname"] = swTranslate($repxml->search_response);
                        $menu[$k]["items"][$k1]["project"] = $project;
                    }
                }
            }
        }
    }

    public function getBootstrapStyle($type)
    {
        if (!$this->bootstrap_styles) {
            return "";
        }

        $x = $this->{"bootstrap_styling_" . $type};
        if ($x) {
            return $x . " ";
        }
    }

/**
 * Function save_admin_password
 *
 * Writes new admin password to the admin config.php
 */
    public function saveAdminPassword($password1, $password2, $language)
    {
        if ($language) {
            ReporticoApp::setConfig("language", $language);
        }

        if ($password1 != $password2) {
            return swTranslate("The passwords are not identical please reenter");
        }

        if (strlen($password1) == 0) {
            return swTranslate("The password may not be blank");
        }

        $proj_parent = findBestLocationInIncludePath($this->admin_projects_folder);
        $proj_dir = $proj_parent . "/admin";
        $proj_conf = $proj_dir . "/config.php";
        $proj_template = $proj_dir . "/adminconfig.template";

        $old_error_handler = set_error_handler("Reportico\ErrorHandler", 0);
        if (!@file_exists($proj_parent)) {
            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
            return "Projects area $proj_parent does not exist - cannot write project";
        }

        if (@file_exists($proj_conf)) {
            if (!is_writeable($proj_conf)) {
                $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                return "Projects config file $proj_conf is not writeable - cannot write config file - change permissions to continue";
            }
        }

        if (!is_writeable($proj_dir)) {
            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
            return "Projects area $proj_dir is not writeable - cannot write project password in config.php - change permissions to continue";
        }

        if (!@file_exists($proj_conf)) {
            if (!@file_exists($proj_template)) {
                $old_error_handler = set_error_handler("Reportico\ErrorHandler");
                return "Projects config template file $proj_template does not exist - please contact reportico.org";
            }
        }

        $old_error_handler = set_error_handler("Reportico\ErrorHandler");

        if (@file_exists($proj_conf)) {
            $txt = file_get_contents($proj_conf);
        } else {
            $txt = file_get_contents($proj_template);
        }

        $proj_language = findBestLocationInIncludePath("language");
        $lang_dir = $proj_language . "/" . $language;
        if (!is_dir($lang_dir)) {
            return "Language directory $language does not exist within the language folder";
        }

        $txt = preg_replace("/(define.*?SW_ADMIN_PASSWORD',).*\);/", "$1'$password1');", $txt);
        $txt = preg_replace ( "/(ReporticoApp::setConfig\(.admin_password.,).*/", "$1'$password1');", $txt);
        $txt = preg_replace ( "/(ReporticoApp::setConfig\(.language.,).*/", "$1'$language');", $txt);

        unsetReporticoSessionParam('admin_password');
        $retval = file_put_contents($proj_conf, $txt);

        // Password is saved so use it so user can login
        if (!ReporticoApp::isSetConfig('admin_password')) {
            ReporticoApp::setConfig("admin_password", $password1);
        } else {
            ReporticoApp::setConfig("admin_password_reset", $password1);
        }

        return;

    }

/**
 * Function setProjectEnvironment
 *
 * Analyses configuration and current session to identify which project area
 * is to be used.
 * If a project is specified in the HTTP parameters then that is used, otherwise
 * the current SESSION
 * "reports" project is used
 */
    public function setProjectEnvironment($initial_project = false, $project_folder = "projects", $admin_project_folder = "projects")
    {
        global $g_projpath;

        $target_menu = "";
        $project = "";

        $last_project = "";

        if (issetReporticoSessionParam("project")) {
            if (getReporticoSessionParam("project")) {
                $last_project = getReporticoSessionParam("project");
            }
        }

        if (!$project && array_key_exists("submit_delete_project", $_REQUEST)) {
            $project = getRequestItem("jump_to_delete_project", "");
            $_REQUEST["xmlin"] = "deleteproject.xml";
            setReporticoSessionParam("project", $project);
        }

        if (!$project && array_key_exists("submit_configure_project", $_REQUEST)) {
            $project = getRequestItem("jump_to_configure_project", "");
            $_REQUEST["xmlin"] = "configureproject.xml";
            setReporticoSessionParam("project", $project);
        }

        if (!$project && array_key_exists("submit_menu_project", $_REQUEST)) {
            $project = getRequestItem("jump_to_menu_project", "");
            setReporticoSessionParam("project", $project);
        }

        if (!$project && array_key_exists("submit_design_project", $_REQUEST)) {
            $project = getRequestItem("jump_to_design_project", "");
            setReporticoSessionParam("project", $project);
        }

        if ($initial_project) {
            $project = $initial_project;
            setReporticoSessionParam("project", $project);
        }

        if (!$project) {
            $project = sessionRequestItem("project", "admin");
        }

        if (!$target_menu) {
            $target_menu = sessionRequestItem("target_menu", "");
        }

        $menu = false;
        $menu_title = "Set Menu Title";

        if ($project == "admin") {
            $project_folder = $admin_project_folder;
        }

        // Now we now the project include the relevant config.php
        $projpath = $project_folder . "/" . $project;

        $configfile = $projpath . "/config.php";
        $configtemplatefile = $projpath . "/adminconfig.template";

        $menufile = $projpath . "/menu.php";
        if ($target_menu != "") {
            $menufile = $projpath . "/menu_" . $target_menu . ".php";
        }

        if (!is_file($projpath)) {
            findFileToInclude($projpath, $projpath);
        }

        setReporticoSessionParam("project_path", $projpath);
        $this->reports_path = $projpath;

        if (!$projpath) {
            findFileToInclude("config.php", $configfile);
            if (ReporticoApp::get("included_config") && ReporticoApp::get("included_config") != $configfile) {
                handleError("Cannot load two different instances on a single page from different projects.", E_USER_ERROR);
            } else {
                ReporticoApp::set("included_config", $configfile);
                include_once $configfile;
            }
            $g_projpath = false;
            ReporticoApp::setConfig("project", false);
            ReporticoApp::setConfig("static_menu", false);
            ReporticoApp::setConfig("admin_menu", false);
            ReporticoApp::set('menu_title', '');
            ReporticoApp::setConfig("dropdown_menu", false);
            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
            handleError("Project Directory $project not found. Check INCLUDE_PATH or project name");
            return;
        }

        $g_projpath = $projpath;
        if (!is_file($configfile)) {
            findFileToInclude($configfile, $configfile);
        }

        if (!is_file($menufile)) {
            findFileToInclude($menufile, $menufile);
        }

        if ($project == "admin" && !is_file($configfile)) {
            findFileToInclude($configtemplatefile, $configfile);
        }

        if ($configfile) {
            if (!is_file($configfile)) {
                handleError("Config file $menufile not found in project $project", E_USER_WARNING);
            }

            if (ReporticoApp::get("included_config") && ReporticoApp::get("included_config") != $configfile) {
                handleError("Cannot load two different instances on a single page from different projects.", E_USER_ERROR);
            } else {
                include_once $configfile;
                ReporticoApp::set("included_config", $configfile);
            }

            if (is_file($menufile)) {
                include $menufile;
            }

            //else
            //handleError("Menu Definition file $menufile not found in project $project", E_USER_WARNING);

        } else {
            findFileToInclude("config.php", $configfile);
            if ($configfile) {
                if (ReporticoApp::get("included_config") && ReporticoApp::get("included_config") != $configfile) {
                    handleError("Cannot load two different instances on a single page from different projects.", E_USER_ERROR);
                } else {
                    include_once $configfile;
                    ReporticoApp::set("included_config", $configfile);
                }
            }
            ReporticoApp::set('project', false);
            ReporticoApp::setConfig("static_menu", false);
            ReporticoApp::setConfig("admin_menu", false);
            $g_projpath = false;
            ReporticoApp::set('menu_title', '');
            ReporticoApp::setConfig("dropdown_menu", false);
            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
            handleError("Configuration Definition file config.php not found in project $project", E_USER_ERROR);
        }

        // Ensure a Database and Output Character Set Encoding is set
        if (!ReporticoApp::isSetConfig("db_encoding")) {
            ReporticoApp::setConfig("db_encoding", "UTF8");
        }

        if (!ReporticoApp::isSetConfig("output_encoding")) {
            ReporticoApp::setConfig("output_encoding", "UTF8");
        }

        // Ensure a language is set
        if (!ReporticoApp::isSetConfig("language")) {
            ReporticoApp::setConfig("language", "en_gb");
        }

        if (!ReporticoApp::isSetConfig('project')) {
            ReporticoApp::setConfig('project', $project);
        }

        setReporticoSessionParam("project", $project);

        $language = "en_gb";
        // Default language to first language in avaible_languages
        $langs = availableLanguages();
        if (count($langs) > 0) {
            $language = $langs[0]["value"];
        }

        $config_language = ReporticoApp::getConfig("language", false);
        if ($config_language && $config_language != "PROMPT") {
            $language = sessionRequestItem("reportico_language", $config_language);
        } else {
            $language = sessionRequestItem("reportico_language", "en_gb");
        }

        // language not found the default to first
        $found = false;
        foreach ($langs as $k => $v) {
            if ($v["value"] == $language) {
                $found = true;
                break;
            }
        }
        if (!$found && count($langs) > 0) {
            $language = $langs[0]["value"];
        }

        if (array_key_exists("submit_language", $_REQUEST)) {
            $language = $_REQUEST["jump_to_language"];
            setReporticoSessionParam("reportico_language", $language);
        }
        ReporticoApp::setConfig("language", $language);

        if (isset($menu)) {
            ReporticoApp::setConfig("static_menu", $menu);
        }

        if (isset($admin_menu)) {
            ReporticoApp::setConfig("admin_menu", $menu);
        }

        ReporticoApp::set('menu_title', $menu_title);
        if (isset($dropdown_menu)) {
            ReporticoApp::setConfig("dropdown_menu", $dropdown_menu);
        }

        // Include project specific language translations
        loadProjectLanguagePack($project, outputCharsetToPhpCharset(ReporticoApp::getConfig("output_encoding", "UTF8")));

        return $project;
    }
    // -----------------------------------------------------------------------------
    // Function : load_plugins
    //
    // Scan plugins folder for custom plugin functions and load them into plugins array
    // -----------------------------------------------------------------------------
    public function loadPlugins()
    {
        $plugin_dir = findBestLocationInIncludePath("plugins");

        if (is_dir($plugin_dir)) {
            if ($dh = opendir($plugin_dir)) {
                while (($file = readdir($dh)) !== false) {
                    $plugin = $plugin_dir . "/" . $file;
                    if (is_dir($plugin)) {
                        $plugin_file = $plugin . "/global.php";
                        if (is_file($plugin_file)) {
                            require_once $plugin_file;
                        }
                        $plugin_file = $plugin . "/" . strtolower($this->execute_mode) . ".php";
                        if (is_file($plugin_file)) {
                            require_once $plugin_file;
                        }
                    }
                }
            }
        }

        // Call any plugin initialisation
        $this->applyPlugins("initialize", $this);
    }

    // -----------------------------------------------------------------------------
    // Function : load_plugins
    //
    // Scan plugins folder for custom plugin functions and load them into plugins array
    // -----------------------------------------------------------------------------
    public function applyPlugins($section, $params)
    {
        foreach ($this->plugins as $k => $plugin) {
            if (isset($plugin[$section])) {
                if (isset($plugin[$section]["function"])) {
                    return $plugin[$section]["function"]($params);
                } else if (isset($plugin[$section]["file"])) {
                    require_once $plugin["file"];
                }
            }
        }
    }

    /**
     * Set theme to use
     *
     * @param string $value Theme to use
     * */
    public function setTheme($value)
    {
        if (trim($value) != '') {
            $this->theme = $value;
        }
    }

    private function getTheme()
    {
        $theme = $this->theme;

        if ($theme == '') {
            $theme = 'default';
        }

        return $theme;
    }

    /**
     * Get the path to the template
     *
     * @param $template stricng name of template foile (eg. prepare.tpl)
     * @return string Path to the template including template / custom file.
     */
    public function getTemplatePath($template)
    {
        $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_' . $template);

        // Some calling frameworks require output to be returned
        // for rendering inside web pages .. in this case
        // return_output_to_caller will be set to true

        if (preg_match("/$reportname/", findBestLocationInIncludePath("templates/" . $this->getTheme() . $reportname))) {
            $template = $reportname;
        }

        if (preg_match("/$reportname/", findBestLocationInIncludePath("templates/" . $reportname))) {
            $template = $reportname;
        } else if ($this->user_template) {
            $template = $this->user_template . "_$template";
        }

        //ReporticoLog::getI()->debug("Template : >$template<");
        return $this->getTheme() . '/' . $template;
    }

    public function applyStyleset($type, $styles, $column = false, $mode = false, $condition = false)
    {
        $txt = "";
        $usecolumn = false;
        foreach ($this->columns as $k => $col) {
            if (!$column || $column == $col->query_name) {
                $usecolumn = $col->query_name;
                break;
            }
        }
        if (!$usecolumn) {
            //echo "Apply Styleset Column $column not found<BR>";
            return;
        }
        foreach ($styles as $element => $style) {
            $txt .= "applyStyle('$type', '$element', '$style');";
        }
        if ($condition && $mode) {
            $condition = "$condition && {TARGET_FORMAT} == '$mode'";
        } else if ($mode) {
            $condition = "{TARGET_FORMAT} == '$mode'";
        }

        $this->addAssignment($usecolumn, $txt, $condition, true);

    }

}
// -----------------------------------------------------------------------------