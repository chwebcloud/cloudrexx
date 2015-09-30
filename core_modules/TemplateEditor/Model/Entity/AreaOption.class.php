<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Html\Sigma;

/**
 * Class AreaOption
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class AreaOption extends Option
{

    /**
     * Shows whether the area should be shown
     *
     * @var bool
     */
    protected $active;

    /**
     * @param String $name
     * @param array  $translations
     * @param array  $data
     */
    public function __construct($name, $translations, $data)
    {
        parent::__construct($name, $translations, $data);
        $this->active = $data['active'] == 'true';
    }

    /**
     * Render the option field in the backend.
     *
     * @param Sigma $template
     */
    public function renderOptionField($template)
    {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile(
            $this->cx->getCodeBaseCoreModulePath()
            . '/TemplateEditor/View/Template/Backend/AreaOption.html'
        );
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_VALUE', ($this->active) ? 'checked' : ''
        );
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName
        );
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'area');
        $template->parse('option');
    }

    /**
     * Render the option in the frontend.
     *
     * @param Sigma $template
     */
    public function renderTheme($template)
    {
        $blockName = strtolower('TEMPLATE_EDITOR_' . $this->name);
        if ($template->blockExists($blockName) && $this->active) {
            $template->touchBlock($blockName);
        }
    }

    /**
     * Handle a change of the option.
     *
     * @param array $data Data from frontend javascript
     *
     * @return array Changed data for the frontend javascript
     *
     * @throws OptionValueNotValidException If the data which the option should
     *                                      handle is invalid this exception
     *                                      will be thrown.
     *                                      It gets caught by the JsonData
     *                                      class and gets handled by the
     *                                      javascript callback in the frontend.
     */
    public function handleChange($data)
    {
        if ($data != 'true' && $data != 'false') {
            throw new OptionValueNotValidException('Should be true or false.');
        }
        $this->active = $data;
        return array('active' => $data);
    }


    /**
     * Returns if area is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set is active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public function getValue()
    {
        return array('active' => $this->active);
    }
}