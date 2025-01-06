<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Module;

class Translation
{
    private string $text = '';
    private array $placeholders = [];
    private array $values = [];
    private array $args = [];
    private $translatedText;
    private array $processedPlaceholders = [];
    private array $processedArgs = [];

    /**
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function __()
    {
        $this->clear();

        $args = func_get_args();

        return $this->translate($args);
    }

    /**
     * @param array $args
     *
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function translate(array $args)
    {
        $this->clear();

        $this->parseInput($args);
        $this->parsePlaceholders();

        if (count($this->placeholders) <= 0) {
            array_unshift($this->args, $this->text);

            return call_user_func_array('__', $this->args);
        }

        $this->translatedText = __($this->text);

        !empty($this->values) && $this->replacePlaceholdersByValue();
        !empty($this->args) && $this->replacePlaceholdersByArgs();

        $unprocessedArgs = array_diff($this->args, $this->processedArgs);
        if (!$unprocessedArgs) {
            return $this->translatedText;
        }

        return vsprintf((string)$this->translatedText, $unprocessedArgs);
    }

    /**
     * @return void
     */
    private function clear(): void
    {
        $this->text = '';
        $this->values = [];
        $this->args = [];
        $this->placeholders = [];
        $this->processedPlaceholders = [];
        $this->processedArgs = [];
        $this->translatedText;
    }

    /**
     * @param array $input
     *
     * @return void
     */
    private function parseInput(array $input): void
    {
        $this->text = (string)array_shift($input);

        if (is_array(current($input))) {
            $this->values = array_shift($input);
        }

        array_walk($input, function (&$el) {
            $el === null && $el = (string)$el;
        });

        $this->args = $input;
    }

    /**
     * @return void
     */
    private function parsePlaceholders(): void
    {
        preg_match_all('/%[\w\d]+%/', $this->text, $placeholders);
        $this->placeholders = array_unique($placeholders[0]);
    }

    /**
     * @return void
     */
    private function replacePlaceholdersByValue(): void
    {
        foreach ($this->values as $placeholder => $value) {
            $newText = str_replace('%' . $placeholder . '%', $value, $this->translatedText, $count);

            if ($count <= 0) {
                continue;
            }

            $this->translatedText = $newText;
            $this->processedPlaceholders[] = '%' . $placeholder . '%';
        }
    }

    /**
     * @return void
     */
    private function replacePlaceholdersByArgs(): void
    {
        $unprocessedPlaceholders = array_diff($this->placeholders, $this->processedPlaceholders);
        $unprocessedArgs = $this->args;

        foreach ($unprocessedPlaceholders as $placeholder) {
            $value = array_shift($unprocessedArgs);

            if ($value === null) {
                break;
            }

            $this->translatedText = str_replace($placeholder, $value, $this->translatedText);

            $this->processedPlaceholders[] = $placeholder;
            $this->processedArgs[] = $value;
        }
    }
}
