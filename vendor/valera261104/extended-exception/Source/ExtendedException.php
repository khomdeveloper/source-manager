<?php


namespace ExtendedException;


use BaseClass\StaticStringService;
use Throwable;


/**
 * Class ExtendedException
 * @package valera261104\ExtendedException
 */
abstract class ExtendedException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            json_encode($this->getError($message ?: [])),
            is_int($code) ? $code : 0,
            $previous
        );
    }

    public function getError(array $data = []): array
    {

        $class = get_called_class();

        $shortClassName = StaticStringService::shortClassName($class);

        $key = $class . '::getError';

        return empty($data) ? [
            $key => [
                'en' => ucfirst(StaticStringService::camelCaseToUnderScore($shortClassName, ' '))
            ]
        ] : [
            $key => [
                'en' => ucfirst(StaticStringService::camelCaseToUnderScore($shortClassName,
                        ' ') . '. Data: {{json}}'),
                'data' => [
                    'json' => json_encode($data)
                ]
            ]
        ];

    }

}