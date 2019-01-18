<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 19:20
 */

namespace App\Webtown\WorkflowBundle\Event\Configuration;

use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Symfony\Component\EventDispatcher\Event;

class RegisterEvent extends Event
{
    /**
     * Project path. You can't modify this in this event!
     *
     * @var string
     */
    protected $projectPath;

    /**
     * Project config. You can't modify this in this event! If you want to change it, use the
     * `ConfigurationEvents::BUILD_INIT` eg.
     *
     * @var array
     */
    protected $config;

    /**
     * @var array|BaseRecipe[]
     */
    protected $recipes = [];

    /**
     * RegisterEvent constructor.
     *
     * @param string $projectPath
     * @param array  $config
     */
    public function __construct(string $projectPath, array $config)
    {
        $this->projectPath = $projectPath;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return BaseRecipe[]|array
     */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    /**
     * @param BaseRecipe $recipe
     *
     * @return $this
     */
    public function addRecipe(BaseRecipe $recipe)
    {
        $this->recipes[] = $recipe;

        return $this;
    }
}
