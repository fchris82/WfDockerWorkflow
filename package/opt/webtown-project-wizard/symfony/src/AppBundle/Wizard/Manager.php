<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 16:05.
 */

namespace AppBundle\Wizard;

/**
 * Class Manager.
 *
 * Ezzel a Manager-rel kezeljük igazából a `wizard` taget a service-ek kapcsán. Itt gyűjtjük össze és itt rendezzük az
 * elérhető Wizard service-eket.
 */
class Manager
{
    /**
     * @var array|ManagerItem[][]
     */
    protected $wizards = [];

    public function addWizard(WizardInterface $wizard, $group, $priority = 0)
    {
        if (!array_key_exists($priority, $this->wizards)) {
            $this->wizards[(int) $priority] = [];
        }

        $this->wizards[(int) $priority][] = new ManagerItem($group, $wizard);
    }

    /**
     * <code>
     *  $wizards = [
     *      'group1' => [
     *          'name1' => $wizard1,
     *          'name2' => $wizard2,
     *      ],
     *      'group2' => [
     *          'name3' => $wizard3,
     *          'name1' => $wizard1,
     *      ],
     *  ]
     * </code>.
     *
     * @return array|WizardInterface[]
     */
    public function getWizards()
    {
        // A prioritás alapján fordítva sorrendezünk, azaz a nagyobb szám kerüljön előrébb.
        krsort($this->wizards);
        $wizards = [];
        // A group szerint csoportosítjük a priority alapján már sorrendbe rakott Wizardokat.
        foreach ($this->wizards as $priority => $priorityWizards) {
            /** @var ManagerItem $item */
            foreach ($priorityWizards as $item) {
                $wizards[$item->getGroup()][$item->getWizard()->getName()] = $item->getWizard();
            }
        }

        return $wizards;
    }
}
