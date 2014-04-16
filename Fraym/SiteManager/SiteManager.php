<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\SiteManager;

/**
 * Class SiteManager
 * @package Fraym\SiteManager
 * @Injectable(lazy=true)
 */
class SiteManager
{
    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    protected $template;

    /**
     * Add block xml before </body> to add the site manager admin panel
     */
    public function addAdminPanel()
    {
        if ($this->user->isAdmin()) {
            $this->template->addFootData(
                $this->blockParser->parse(
                    '<block type="module" permission="user" editable="false"><class>\Fraym\SiteManager\SiteManagerController</class><method>adminPanelInit</method><checkRoute>checkRoute</checkRoute></block>'
                )
            );
        }
    }
}
