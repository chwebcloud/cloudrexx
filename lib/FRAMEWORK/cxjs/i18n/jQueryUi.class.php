<?php
class JQueryUiI18nProvider implements ContrexxJavascriptI18nProvider {
    public getVariables($langCode) {
        $vars = array();
        $datePickerFile = '/lib/javascript/jquery/ui/i18n/jquery.ui.datepicker-'.$langCode.'.js';
        if(file_exists(ASCMS_DOCUMENT_ROOT).$datePickerFile)
            $vars['datePicker'] = $datePickerFile;
        return $vars;
    }
}