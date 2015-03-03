<?php
/*
 * This file is part of Pomm's ModelManager package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\ModelManager\Generator;

use PommProject\Foundation\Inflector;
use PommProject\Foundation\ParameterHolder;

/**
 * EntityGenerator
 *
 * Entity generator.
 *
 * @package ModelManager
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see BaseGenerator
 */
class EntityGenerator extends BaseGenerator
{
    /**
     * generate
     *
     * Generate Entity file.
     *
     * @see BaseGenerator
     */
    public function generate(ParameterHolder $input, array $output = [])
    {
        $this
            ->checkOverwrite($input)
            ->outputFileCreation($output)
            ->saveFile(
                $this->filename,
                $this->mergeTemplate(
                    [
                        'namespace' => $this->namespace,
                        'entity'    => Inflector::studlyCaps($this->relation),
                        'relation'  => $this->relation,
                        'schema'    => $this->schema,
                        'doc_properties' => $this->createDocProperties(),
                        'flexible_container' => $this->flexible_container,
                        'flexible_container_class' => array_reverse(explode('\\', $this->flexible_container))[0]
                    ]
                )
            );

        return $output;
    }

    /**
     * formatDocProperties
     *
     * @access protected
     * @return string
     */
    protected function createDocProperties()
    {
        $table_oid          = $this->checkRelationInformation();
        $field_informations = $this->getFieldInformation($table_oid);

        $comments = [];
        foreach ($field_informations as $info) {

            $converterClient = $this->getSession()
                ->getPoolerForType('converter')
                ->getClient($info['type']);

            $comments[] = sprintf(" * @property %s $%s"
                , $converterClient->toPhpType()
                , $info['name']
            );
        }

        return count($comments) > 0 ? join("\n", $comments) : ' *';
    }

    /**
     * getCodeTemplate
     *
     * @see BaseGenerator
     */
    protected function getCodeTemplate()
    {
        return <<<'_'
<?php

namespace {:namespace:};

use {:flexible_container:};

/**
 * {:entity:}
 *
 * Flexible entity for relation
 * {:schema:}.{:relation:}
 *
 * @see FlexibleEntity
 *
{:doc_properties:}
 */
class {:entity:} extends {:flexible_container_class:}
{
}

_;
    }
}
