<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;

/**
 * Class StorableTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class StorableTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase
{
    /**
     * @var \Cx\Core_Modules\TemplateEditor\Model\Entity\TestStorage
     */
    protected $testStorage;

    /**
     * @var \Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository
     */
    protected $themeOptionRepository;

    protected function setUp()
    {
        $this->testStorage =
            new \Cx\Core_Modules\TemplateEditor\Model\Entity\TestStorage();
        $this->themeOptionRepository =
            new \Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository(
                $this->testStorage
            );
    }

    public function testLoadOption()
    {
        $themeOption = $this->themeOptionRepository->get(
            new \Cx\Core\View\Model\Entity\Theme(
                null,
                null,
                '/core_modules/TemplateEditor/Testing/UnitTest/Test_Template'
            )
        );
        $isOptionSet = is_a(
            $themeOption,
            '\Cx\Core_Modules\TemplateEditor\Model\Entity\OptionSet',
            true
        );
        $this->assertTrue($isOptionSet);
        if ($isOptionSet) {
            $this->assertTrue(
                is_a(
                    'Cx\Core_Modules\TemplateEditor\Model\Entity\ColorOption',
                    $themeOption->getOption('main_color')->getType(),
                    true
                )
            );
        }
    }

    public function testSaveOption()
    {
        $themeOption = $this->themeOptionRepository->get(
            new \Cx\Core\View\Model\Entity\Theme(
                null,
                null,
                '/core_modules/TemplateEditor/Testing/UnitTest/Test_Template'
            )
        );
        $newColor = '#dddddd';
        $isOptionSet = is_a(
            $themeOption,
            '\Cx\Core_Modules\TemplateEditor\Model\Entity\OptionSet',
            true
        );
        $this->assertTrue($isOptionSet);
        if ($isOptionSet) {
            /**
             * @var $color \Cx\Core_Modules\TemplateEditor\Model\Entity\ColorOption
             */
            $color = $themeOption->getOption('main_color');
            $color->handleChange($newColor);
            $this->assertTrue($color->getColor() == $newColor);
        }

        $this->assertTrue($this->themeOptionRepository->save($themeOption));
    }

}