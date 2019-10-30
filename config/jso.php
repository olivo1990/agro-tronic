<?php

  class phpJSO {

    /// properties

    /**
     *  JavaScript's original source code.
    */
    var $_jsSourceCode = null;

    /**
     *  Size of $_jsSourceCode.
    */
    var $_jsSourceSize = null;

    /**
     *  JavaScript's obfuscated source code.
    */
    var $_obfuscatedCode = null;

    /**
     *  When walking through the code, this gives current position.
    */
    var $_position = null;

    /**
     *  When walking through the code, this gives previous character.
    */
    var $_charFirst = null;

    /**
     *  When walking through the code, this gives current character.
    */
    var $_charSecond = null;

    /**
     *  When walking through the code, this gives next character.
    */
    var $_charNext = null;

    /**
     *  Errors reported while walking through the source code.
    */
    var $_errors = null;

    /// constructor

    function phpJSO( ) {
      $this->freeMemory( );
    }

    /// public methods

    function setSourceCode( $_jsSourceCode ) {
      $this->_jsSourceCode  =& $_jsSourceCode;
      $this->_position      =  0;
      $this->_jsSourceSize  =  strlen( $_jsSourceCode );
    }

    function setSourceCodeByRef( &$_jsSourceCode ) {
      $this->_jsSourceCode  =& $_jsSourceCode;
      $this->_position      =  0;
      $this->_jsSourceSize  =  strlen( $_jsSourceCode );
    }

    function &getCode( ) {
      return $this->_jsSourceCode;
    }

    function &getObfuscatedCode( ) {
      return( null === $this->_obfuscatedCode )? $this->_obfuscateCode( ): $this->_obfuscatedCode;
    }

    function freeMemory( ) {
      $this->_jsSourceCode = null;
      $this->_obfuscatedCode = null;
      $this->_jsSourceSize = null;
      $this->_position = null;
      $this->_charFirst = null;
      $this->_charSecond = null;
      $this->_charNext = null;
      $this->_errors = null;
    }

    /// private methods

    function _isAlphaNum( $char ) {
      return(
        '_' === $char || '$' === $char || '\\' === $char || 126 < ord( $char ) ||
        ( '0' <= $char && '9' >= $char ) ||
        ( 'A' <= $char && 'Z' >= $char ) ||
        ( 'a' <= $char && 'z' >= $char )
      );
    }

    function _getAnotherChar( ) {
      if( null === $this->_position ) {
        $this->_charNext = null;
        return $this->_charNext;
      }
      if( $this->_jsSourceSize <= $this->_position ) {
        $this->_charNext = "\0";
        return $this->_charNext;
      }
      $char = $this->_jsSourceCode{ $this->_position++ };
      $this->_charNext = ( $this->_jsSourceSize <= $this->_position )? "\0": $this->_jsSourceCode{ $this->_position };
      if( ' ' <= $char || "\n" === $char || "\0" === $char ) {
        return $char;
      }
      if( $char === "\r" ) {
        return "\n";
      }
      return ' ';
    }

    function _getSecondChar( ) {
      $char = $this->_getAnotherChar( );
      if( '/' === $char ) {
        switch( $this->_charNext ) {
          case '/':
            for( ; ; ) {
              $char = $this->_getAnotherChar( );
              if( "\n" >= $char ) {
                return $char;
              }
            }

          case '*':
            $this->_getAnotherChar( );
            for( ; ; ) {
              switch( $this->_getAnotherChar( ) ) {
                case '*':
                  if( '/' === $this->_charNext ) {
                    $this->_getAnotherChar( );
                    return ' ';
                  }
                  break;

                case "\0":
                  $this->_errors[ ] = 'Unterminated comment at '.$this->_position;
                  return null;
              }
            }

          default:
            return $char;
        }
      }

      return $char;
    }

    function _multiActionFunc( $action, $char = '' ) {
      switch( $action ) {
        case 0:
          $this->_charFirst .= $char;
          $this->_charSecond = '';

        case 1:
          $this->_obfuscatedCode .= $this->_charFirst;

        case 2:
          $this->_charFirst = $this->_charSecond;
          if( '\'' === $this->_charFirst || '"' === $this->_charFirst ) {
            for( ; ; ) {
              $this->_obfuscatedCode .= $this->_charFirst;
              $this->_charFirst = $this->_getAnotherChar( );
              if( $this->_charFirst == $this->_charSecond ) {
                break;
              }
              if( "\n" >= $this->_charFirst ) {
                $this->_errors[ ] = 'Unterminated string literal ('.$this->_charFirst.') at '.$this->_position;
                return;
              }
              if( '\\' === $this->_charFirst ) {
                $this->_obfuscatedCode .= $this->_charFirst;
                $this->_charFirst = $this->_getAnotherChar( );
              }
            }
          }

        case 3:
          $this->_charSecond = $this->_getSecondChar( );
          if( '/' === $this->_charSecond && ( '(' === $this->_charFirst || ',' === $this->_charFirst || '=' === $this->_charFirst ) ) {
            $this->_obfuscatedCode .= $this->_charFirst.$this->_charSecond;
            for( ; ; ) {
              $this->_charFirst = $this->_getAnotherChar( );
              if( '/' === $this->_charFirst ) {
                break;
              } elseif( '\\' === $this->_charFirst ) {
                $this->_obfuscatedCode .= $this->_charFirst;
                $this->_charFirst = $this->_getAnotherChar( );
              } elseif( "\n" >= $this->_charFirst ) {
                $this->_errors[ ] = 'Unterminated Regular Expression literal at '.$this->_position;
                return;
              }
              $this->_obfuscatedCode .= $this->_charFirst;
            }
            $this->_charSecond = $this->_getSecondChar( );
          }

        default:
          break;
      }
    }

    function &_obfuscateCode( ) {
      $this->_charFirst = '';
      $this->_obfuscatedCode = '/*** Powered by phpJSO -- http://www.comrax.com/phpJSO ***/'."\n";

      $this->_multiActionFunc( 3 );
      while( "\0" !== $this->_charFirst ) {
        switch( $this->_charFirst ) {
        case ' ':
          if( $this->_isAlphaNum( $this->_charSecond ) ) {
            $this->_multiActionFunc( 1 );
          } else {
            $this->_multiActionFunc( 2 );
          }
          break;

        case "\n":
          switch( $this->_charSecond ) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
              $this->_multiActionFunc( 1 );
              break;

            case ' ':
              $this->_multiActionFunc( 3 );
              break;

            default:
              if( $this->_isAlphaNum( $this->_charSecond ) ) {
                $this->_multiActionFunc( 1 );
              } else {
                $this->_multiActionFunc( 2 );
              }
              break;
          }
          break;

        default:
          switch( $this->_charSecond ) {
            case ' ':
              if( $this->_isAlphaNum( $this->_charFirst ) ) {
                $this->_multiActionFunc( 1 );
                break;
              }
              $this->_multiActionFunc( 3 );
              break;

            case "\n":
              switch( $this->_charFirst ) {
                case ']':
                  $this->_multiActionFunc( 1 );
                  break;

                case ')':
                case '-':
                case '+':
                case '\'':
                case '"':
                  $this->_multiActionFunc( 0 );
                  break;

                case '}':
                  $this->_multiActionFunc( 0, ';' );
                  break;

                default:
                  if( $this->_isAlphaNum( $this->_charFirst ) ) {
                    $this->_multiActionFunc( 0, ' ' );
                  } else {
                    $this->_multiActionFunc( 3 );
                  }
              }
              break;

            default:
              $this->_multiActionFunc( 1 );
              break;
          }
        }
      }

      return $this->_obfuscatedCode;
    }

  }



  /**
   *  DON'T ADD ANY CLOSING TAG OR CODE HENCEFORTH!
   *
   *  PHP ENGINE AUTOMATICALLY STOPS PARSING AT END
   *  OF SCRIPT, EVEN IF NO CLOSING TAG IS PRESENT.
  */
