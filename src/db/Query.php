<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.com/license
 */

namespace craft\db;

use Craft;
use craft\helpers\ArrayHelper;
use yii\base\Exception;
use yii\db\Connection as YiiConnection;

/**
 * Class Query
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class Query extends \yii\db\Query
{
    // Public Methods
    // =========================================================================

    /**
     * Returns whether a given table has been joined in this query.
     *
     * @param string $table
     *
     * @return bool
     */
    public function isJoined(string $table): bool
    {
        foreach ($this->join as $join) {
            if ($join[1] === $table || strpos($join[1], $table) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function where($condition, $params = [])
    {
        if (!$condition) {
            $condition = null;
        }

        return parent::where($condition, $params);
    }

    /**
     * @inheritdoc
     */
    public function andWhere($condition, $params = [])
    {
        if (!$condition) {
            return $this;
        }

        return parent::andWhere($condition, $params);
    }

    /**
     * @inheritdoc
     */
    public function orWhere($condition, $params = [])
    {
        if (!$condition) {
            return $this;
        }

        return parent::orWhere($condition, $params);
    }

    // Execution functions
    // -------------------------------------------------------------------------

    /**
     * Executes the query and returns the first two columns in the results as key/value pairs.
     *
     * @param YiiConnection|null $db The database connection used to execute the query.
     *                               If this parameter is not given, the `db` application component will be used.
     *
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     * @throws Exception if less than two columns were selected
     */
    public function pairs(YiiConnection $db = null): array
    {
        try {
            $rows = $this->createCommand($db)->queryAll();
        } catch (QueryAbortedException $e) {
            Craft::$app->getErrorHandler()->logException($e);

            return [];
        }

        if (!empty($rows)) {
            $columns = array_keys($rows[0]);

            if (count($columns) < 2) {
                throw new Exception('Less than two columns were selected');
            }

            $rows = ArrayHelper::map($rows, $columns[0], $columns[1]);
        }

        return $rows;
    }

    /**
     * @inheritdoc
     */
    public function all($db = null)
    {
        try {
            return parent::all($db);
        } catch (QueryAbortedException $e) {
            Craft::$app->getErrorHandler()->logException($e);

            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function one($db = null)
    {
        try {
            return parent::one($db);
        } catch (QueryAbortedException $e) {
            Craft::$app->getErrorHandler()->logException($e);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function scalar($db = null)
    {
        try {
            return parent::scalar($db);
        } catch (QueryAbortedException $e) {
            Craft::$app->getErrorHandler()->logException($e);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function column($db = null)
    {
        try {
            return parent::column($db);
        } catch (QueryAbortedException $e) {
            Craft::$app->getErrorHandler()->logException($e);

            return [];
        }
    }

    /**
     * Executes the query and returns a single row of result at a given offset.
     *
     * @param int                $n  The offset of the row to return. If [[offset]] is set, $offset will be added to it.
     * @param YiiConnection|null $db The database connection used to generate the SQL statement.
     *                               If this parameter is not given, the `db` application component will be used.
     *
     * @return array|bool The row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     */
    public function nth(int $n, YiiConnection $db = null)
    {
        $offset = $this->offset;
        $this->offset = ($offset ?: 0) + $n;
        $result = $this->one($db);
        $this->offset = $offset;

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function queryScalar($selectExpression, $db)
    {
        try {
            return parent::queryScalar($selectExpression, $db);
        } catch (QueryAbortedException $e) {
            Craft::$app->getErrorHandler()->logException($e);

            return false;
        }
    }
}
