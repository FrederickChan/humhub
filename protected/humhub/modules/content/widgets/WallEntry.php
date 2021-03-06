<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\space\models\Space;

/**
 * WallEntry is responsible to show a content inside a stream/wall.
 * 
 * @see \humhub\modules\content\components\ContentActiveRecord
 * @since 0.20
 * @author luke
 */
class WallEntry extends Widget
{

    /**
     * Edit form is loaded to the wallentry itself.
     */
    const EDIT_MODE_INLINE = 'inline';

    /**
     * Opens the edit page in a new window.
     */
    const EDIT_MODE_NEW_WINDOW = 'new_window';

    /**
     * Edit form is loaded into a modal.
     */
    const EDIT_MODE_MODAL = 'modal';

    /**
     * The content object
     *
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $contentObject;

    /**
     * Indicates the post was just edited
     *
     * @var boolean
     */
    public $justEdited = false;

    /**
     * Route to edit the content
     * 
     * @var string
     */
    public $editRoute = "";

    /**
     * Defines the way the edit of this wallentry is displayed.
     * 
     * @var type 
     */
    public $editMode = self::EDIT_MODE_INLINE;

    /**
     * The wall entry layout to use
     * 
     * @var string
     */
    public $wallEntryLayout = "@humhub/modules/content/widgets/views/wallEntry.php";

    /**
     * @deprecated since version 1.2 use file model 'show_in_stream' attribute instead
     * @var boolean show files widget containing a list of all assigned files
     */
    public $showFiles = true;

    /**
     * @inheritdoc
     */
    public static function widget($config = [])
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $config['class'] = get_called_class();
            $widget = Yii::createObject($config);
            $out = $widget->render($widget->wallEntryLayout, ['content' => $widget->run(), 'object' => $widget->contentObject, 'wallEntryWidget' => $widget]);
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    /**
     * Returns the edit url to edit the content (if supported)
     * 
     * @return string url
     */
    public function getEditUrl()
    {
        if (empty($this->editRoute)) {
            return;
        }

        // Don't show edit link, when content container is space and archived
        if ($this->contentObject->content->container instanceof Space && $this->contentObject->content->container->status == Space::STATUS_ARCHIVED) {
            return "";
        }

        return $this->contentObject->content->container->createUrl($this->editRoute, ['id' => $this->contentObject->id]);
    }

    /**
     * Returns an array of contextmenu items either in form of a single array:
     * 
     * ['label' => 'mylabel', icon => 'fa-myicon', 'data-action-click' => 'myaction', ...]
     * 
     * or as widget type definition:
     * 
     * [MyWidget::class, [...], [...]]
     * 
     * If an $editRoute is set this function will include an edit button.
     * The edit logic can be changed by changing the $editMode.
     * 
     * @return array
     * @since 1.2
     */
    public function getContextMenu()
    {
        $result = [];
        if (!empty($this->getEditUrl())) {
            $result[] = [EditLink::class, ['model' => $this->contentObject, 'mode' => $this->editMode, 'url' => $this->getEditUrl()], ['sortOrder' => 200]];
        }
        return $result;
    }

    /**
     * Renders the wall entry output 
     * 
     * @return string the output
     * @throws \Exception
     */
    public function renderWallEntry()
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $out = $this->render($this->wallEntryLayout, [
                'content' => $this->run(),
                'object' => $this->contentObject,
                'wallEntryWidget' => $this
            ]);
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() . $out;
    }

}
