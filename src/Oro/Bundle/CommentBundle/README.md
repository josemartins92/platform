OroCommentBundle
===================

The `OroCommentBundle` provide ability to comment activity entities. The system administrator could manage this functionality on *System / Entities / Entity Management* page.

How to enable comment association with new activity entity using migrations
---------------------------------------------------------------------------

Usually you do not need to provide predefined set of associations between the comment entity and activity entities, rather it is the administrator chose to do this. But it is possible to create this type of association using migrations if you need. The following example shows how it can be done:

``` php
<?php
...
class AcmeBundle implements Migration, CommentExtensionAwareInterface
{
    /** @var CommentExtension */
    protected $comment;

    /**
     * @param CommentExtension $commentExtension
     */
    public function setCommentExtension(CommentExtension $commentExtension)
    {
        $this->comment = $commentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addComment($schema, $this->comment);
    }

    /**
     * @param Schema           $schema
     * @param CommentExtension $commentExtension
     */
    public static function addComment(Schema $schema, CommentExtension $commentExtension)
    {
        $commentExtension->addCommentAssociation($schema, 'acme_entity');
    }
}
```
CommentExtension performs association of the activity entity with comment. To get CommentExtension in migration you should implement CommentExtensionAwareInterface.

How to enable comments on new activity entity list widget
---------------------------------------------------------

If you created the new activity entity and want to comment it in activity list widget you need implement CommentProviderInterface. An example:

```
class AcmeActivityListProvider implements ActivityListProviderInterface, CommentProviderInterface
{
...
/**
 * {@inheritdoc}
 */
public function hasComments(ConfigManager $configManager, $entity)
{
    $config = $configManager->getProvider('comment')->getConfig($entity);
    return $config->is('enabled');
}
...
```
The comment widget will be rendered into ```div.message .comment``` node of js/activityItemTemplate.js.twig template.