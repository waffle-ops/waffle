<?php

namespace Waffle\Model\Audit\AuditCheck\Drupal;

use Symfony\Component\Finder\Finder;
use Waffle\Model\Audit\BaseAuditCheck;
use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Context\Context;
use Waffle\Traits\DrupalContextTrait;

class BasicAuth extends BaseAuditCheck
{
    use DrupalContextTrait;

    /**
     * @var Drush
     */
    private $drush;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Drush $drush
     */
    public function __construct(
        Context $context,
        Drush $drush
    ) {
        $this->drush = $drush;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(): bool
    {
        return $this->isDrupal();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        // @todo This is not a great test, but is at least enough to show the
        // thought process around these audit checks.
        $finder = new Finder();
        $finder->files();
        $finder->in([
            getcwd(),
        ]);
        $finder->name('settings.php');

        $iterator = $finder->getIterator();
        $iterator->rewind();
        $file = $iterator->current();

        $contents = file_get_contents($file->getRealPath());
        return strpos($contents, 'PHP_AUTH_USER') === false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Ensure basic authentication is not implemented in settings.php.';
    }

    /**
     * {@inheritdoc}
     */
    public function getResolution(): string
    {
        return sprintf(
            "%s\n%s",
            'Remove the basic authentication implementation out of settings.php.',
            'Consider using the shield module instead.'
        );
    }
}
