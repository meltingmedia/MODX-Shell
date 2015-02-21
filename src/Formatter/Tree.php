<?php namespace MODX\Shell\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * An helper to render a tree
 */
class Tree
{
    /**
     * @var string|Callable
     */
    protected $value = 'name';
    /**
     * @var string
     */
    protected $children = 'children';
    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string|Callable $value
     */
    public function setValueField($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     */
    public function setChildrenField($value)
    {
        $this->children = $value;
    }

    /**
     * @param array $tree
     */
    public function render(array $tree)
    {
        $idx = 0;
        foreach ($tree as $item) {
            $this->renderItem($item, $idx);
        }
    }

    /**
     * @param array $item
     * @param int $idx
     */
    protected function renderItem(array $item, $idx = 0)
    {
        $separator = str_repeat('<info>|</info>   ', $idx) . '<info>|-</info> ';
        $this->output->writeln($separator.$this->getItemLabel($item));
        if (isset($item[$this->children]) && !empty($item[$this->children])) {
            $idx++;
            foreach ($item[$this->children] as $item) {
                $this->renderItem($item, $idx);
            }
        }
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function getItemLabel(array $item)
    {
        if (is_callable($this->value)) {
            return call_user_func_array($this->value, array($item));
        }

        return isset($item[$this->value]) ?: 'error';
    }
}
