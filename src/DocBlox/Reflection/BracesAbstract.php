<?php
/**
 * DocBlox
 *
 * @category   DocBlox
 * @package    Static_Reflection
 * @copyright  Copyright (c) 2010-2010 Mike van Riel / Naenius. (http://www.naenius.com)
 */

/**
 * Parses a method or function Argument.
 *
 * @category   DocBlox
 * @package    Static_Reflection
 * @subpackage Base
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 */
abstract class DocBlox_Reflection_BracesAbstract extends DocBlox_Reflection_DocBlockedAbstract
{
  /**
   * Generic method which iterates through all tokens between the braces following the current position in the token
   * iterator.
   *
   * Please note: This method will also move the cursor position in the token iterator forward.
   * When a token is encountered this method will invoke the processToken method, which is defined in the
   * DocBlox_Reflection_Abstract class. Literals are ignored.
   *
   * @see    DocBlox_Reflection_Abstract
   *
   * @param  DocBlox_TokenIterator $tokens
   *
   * @return int[]
   */
  public function processTokens(DocBlox_TokenIterator $tokens)
  {
    $level = -1;
    $start = 0;
    $end   = 0;
    $token = null;

    // parse class contents
    $this->debug('>> Processing tokens');
    while ($tokens->valid())
    {
        /** @var DocBlox_Token $token */
      $token = $token === null ? $tokens->current() : $tokens->next();

      // if we encounter a semi-colon before we have an opening brace then this is an abstract or interface function
      // which have no body; stop looking!
      if (($token instanceof DocBlox_Token)
        && ($token->getType() === null)
        && ($token->getContent() === ';')
        && ($level === -1))
      {
        return array($start, $end);
      }

      // determine where the 'braced' section starts and end.
      // the first open brace encountered is considered the opening brace for the block and processing will
      // be 'breaked' when the closing brace is encountered
      if ($token
          && (!$token->getType()
              || ($token->getType() == T_CURLY_OPEN)
              || ($token->getType() == T_DOLLAR_OPEN_CURLY_BRACES))
          && (($token->getContent() == '{')
              || (($token->getContent() == '}'))))
      {
        switch ($token->getContent())
        {
          case '{':
            // expect the first brace to be an opening brace
            if ($level == -1)
            {
              $level++;
              $start = $tokens->key();
            }
            $level++;
            break;
          case '}':
            if ($level == -1) continue;
            $level--;

            // reached the end; break from the while
            if ($level === 0)
            {
              $end = $tokens->key();
              break 2; // time to say goodbye
            }
            break;
        }
        continue;
      }

      if ($token && $token->getType())
      {
        // if a token is encountered and it is not a literal, invoke the processToken method
        $this->processToken($token, $tokens);
      }
    }

    // return the start and end token index
    return array($start, $end);
  }

}