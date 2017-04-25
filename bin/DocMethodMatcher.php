<?php

namespace Zendesk;

use InvalidArgumentException;
use Psy\TabCompletion\Matcher\AbstractContextAwareMatcher;
use Psy\TabCompletion\Matcher\AbstractMatcher;

class DocMethodMatcher extends AbstractContextAwareMatcher
{

    /**
     * Provide tab completion matches for readline input.
     *
     * @param array $tokens information substracted with get_token_all
     * @param array $info readline_info object
     *
     * @return array The matches resulting from the query
     */
    public function getMatches(array $tokens, array $info = [])
    {
        $input = $this->getInput($tokens);

        $firstToken = array_pop($tokens);
        if (self::tokenIs($firstToken, self::T_STRING)) {
            // second token is the object operator
            array_pop($tokens);
        }
        $objectToken = array_pop($tokens);

        if (! is_array($objectToken)) {
            return [];
        }
        $objectName = str_replace('$', '', $objectToken[1]);

        try {
            $object = $this->getVariable($objectName);
        } catch (InvalidArgumentException $e) {
            return [];
        }

        return array_filter(
            array_keys($object->getValidSubResources()),
            function ($var) use ($input) {
                return AbstractMatcher::startsWith($input, $var);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatched(array $tokens)
    {
        $token = array_pop($tokens);
        $prevToken = array_pop($tokens);

        switch (true) {
            case self::tokenIs($token, self::T_OBJECT_OPERATOR):
            case self::tokenIs($prevToken, self::T_OBJECT_OPERATOR):
                return true;
        }

        return false;
    }
}
