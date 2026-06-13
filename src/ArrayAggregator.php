<?php

namespace Ocallit\Utils;

class ArrayAggregator {

    /**
     * Segregates an array into deep buckets.
     * * @param array $array The data to categorize.
     * @param array $groupBy An array where values are either string/int keys, or callables.
     * Callables receive:($row, $ruleKey, $groupBy).
     * @param array|null $remainingKeys Internal use only for recursion state.
     */
    public static function categorize(array $array, array $groupBy, ?array $remainingKeys = null): array {
        // Initialize the tracking keys on the first pass
        if($remainingKeys === null) {
            $remainingKeys = array_keys($groupBy);
        }

        if(empty($remainingKeys)) {
            return $array;
        }

        // Grab the current rule's key and the rule itself
        $currentRuleKey = array_shift($remainingKeys);
        $rule = $groupBy[$currentRuleKey];
        $result = [];

        foreach($array as $item) {
            if(is_callable($rule)) {
                // Execute callable with the exact signature requested
                $key = $rule($item, $currentRuleKey, $groupBy);
            } else {
                // Standard array key lookup
                $key = $item[$rule] ?? '';
            }

            $result[(string)$key][] = $item;
        }

        // Recursion for the next levels
        if(!empty($remainingKeys)) {
            foreach($result as $bucketKey => $bucketItems) {
                $result[$bucketKey] = self::categorize($bucketItems, $groupBy, $remainingKeys);
            }
        }

        return $result;
    }

    /**
     * Groups array data and performs SQL-like aggregations.
     */
    public static function aggregateBy(array $array, array $groupBy, array $operations): array {
        if(empty($groupBy)) {
            throw new \InvalidArgumentException('groupBy array cannot be empty.');
        }

        $buckets = self::categorize($array, $groupBy);
        $depth = count($groupBy);

        $applyMath = function(array $group, int $currentDepth) use(&$applyMath, $operations) {
            if($currentDepth > 1) {
                $nestedResult = [];
                foreach($group as $key => $subGroup) {
                    $nestedResult[$key] = $applyMath($subGroup, $currentDepth - 1);
                }
                return $nestedResult;
            }

            $groupResult = [];
            $columns = [];

            foreach($operations as $outKey => [$inField, $op]) {
                if(!isset($columns[$inField])) {
                    $columns[$inField] = array_column($group, $inField);
                }

                $values = $columns[$inField];
                $count = count($values);

                switch(strtolower($op)) {
                    case 'sum':
                        $groupResult[$outKey] = array_sum($values);
                        break;
                    case 'max':
                        $groupResult[$outKey] = $count > 0 ? max($values) : null;
                        break;
                    case 'min':
                        $groupResult[$outKey] = $count > 0 ? min($values) : null;
                        break;
                    case 'avg':
                        $groupResult[$outKey] = $count > 0 ? array_sum($values) / $count : 0;
                        break;
                    case 'std':
                    case 'stdpop':
                        if($count < 2) {
                            $groupResult[$outKey] = 0.0;
                            break;
                        }
                        $avg = array_sum($values) / $count;
                        $sqSum = 0.0;
                        foreach($values as $v) {
                            $sqSum +=($v - $avg) ** 2;
                        }
                        $divisor = $op === 'std' ?($count - 1) : $count;
                        $groupResult[$outKey] = sqrt($sqSum / $divisor);
                        break;
                    default:
                        throw new \InvalidArgumentException("Unknown aggregation operation: $op");
                }
            }
            return $groupResult;
        };

        return $applyMath($buckets, $depth);
    }

}
