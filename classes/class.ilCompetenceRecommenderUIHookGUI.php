<?php
declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

include_once("GUI/class.ilCompetenceRecommenderGUI.php");
include_once("class.ilCompetenceRecommenderAlgorithm.php");

/**
 * Class ilCompetenceRecommenderUIHookGUI
 *
 * Generated by srag\PluginGenerator v0.9.7
 *
 * @author Leonie Feldbusch <feldbusl@informatik.uni-freiburg.de>
 *
 * @ilCtrl_Calls ilCompetenceRecommenderUIHookGUI: ilCompetenceRecommenderGUI
 */
class ilCompetenceRecommenderUIHookGUI extends ilUIHookPluginGUI {

	const PLUGIN_CLASS_NAME = ilCompetenceRecommenderPlugin::class;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilAccessHandler
	 */
	var $access;

	/**
	 * @var ilUIFramework
	 */
	var $ui;

	/**
	 * @var ilDB
	 */
	var $db;

	/**
	 * @var ilCompetenceRecommenderPlugin
	 */
	var $pl;


	/**
	 * ilCompetenceRecommenderUIHookGUI constructor
	 */
	public function __construct() {
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->access = $DIC->access();
		$this->ui = $DIC->ui();
		$this->db = $DIC->database();
		$this->pl = ilCompetenceRecommenderPlugin::getInstance();
		$this->pl->includeClass("GUI/class.ilCompetenceRecommenderGUI.php");
	}


	/**
	 * @param string $a_comp
	 * @param string $a_part
	 * @param array  $a_par
	 *
	 * @return array
	 */
	public function getHTML(/*string*/
		$a_comp, /*string*/
		$a_part, /*array*/
		$a_par = []): array 
	{
		if ($a_comp == "Services/PersonalDesktop" && $a_part == "center_column") {
			if (ilCompetenceRecommenderAlgorithm::hasUserProfile()) {
				return ["mode" => ilUIHookPluginGUI::PREPEND, "html" => $this->pdRecommendation()];
			}
		}
		return [ "mode" => ilUIHookPluginGUI::KEEP, "html" => "" ];
	}

	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
	}

	/**
	* write on personal desktop
	*
	* @return string HTML of div
	*/
	function pdRecommendation()
	{
		$renderer = $this->ui->renderer();
		$factory = $this->ui->factory();

		// show recommendations in template
		$pl = $this->getPluginObject();
		$btpl = $pl->getTemplate("tpl.comprecDesktop.html");
		$btpl->setVariable("TITLE", "Meine Lernempfehlungen");

		$data = \ilCompetenceRecommenderAlgorithm::getDataForDesktop();
		$allcards = array();

		foreach ($data as $row) {
			$obj_id = ilObject::_lookupObjectId($row["id"]);
			$link = $renderer->render($factory->link()->standard(ilObject::_lookupTitle($obj_id), ilLink::_getLink($row["id"])));
			$image = $factory->image()->standard(ilObject::_getIcon($obj_id), "Icon");
			$card = $factory->card($link, $image)->withSections(array($factory->legacy($row["title"])));
			array_push($allcards, $card);
		};

		$deck = $factory->deck($allcards);
		$renderedobjects = $renderer->render($deck);

		// render button for dashboard on extra page
		$button = $renderer->render($factory->button()
				->standard("mehr Details", $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class,
					ilCompetenceRecommenderGUI::class], 'show')));

		// append html with a new line
		$compRecRow = "<div class=\"ilObjRow\">" . $renderedobjects . "<div class=\"ilFloatRight\">" .$button . "</div> <br /> <hr /> </div>";
		$btpl->setVariable("COMPRECROW", $compRecRow);
		return $btpl->get();
	}
}
