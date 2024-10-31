<?php
namespace Setka\Editor\Service\SystemReport;

use Symfony\Component\HttpFoundation\Request;

class ServerSuperGlobalSection extends AbstractVariablesSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'PHP $_SERVER variable';

    /**
     * @var Request;
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function build(): array
    {
        $values = array();

        foreach ($this->request->server->all() as $name => &$value) {
            if (is_scalar($value)) {
                $values[$name] =& $value;
            } else {
                $values[$name] = self::VARIABLE_DOESNT_SCALAR;
            }
        }

        unset($values['HTTP_COOKIE']);

        return $values;
    }
}
