<?php

namespace spec\Aya\Helper;

use Aya\Helper\Text;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TextSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Text::class);
    }

    function it_converts_text_to_pascal_case() {
        $this->toPascalCase('show-by-category')->shouldReturn('showByCategory');
    }

    function it_converts_text_to_camel_case() {
        $this->toCamelCase('article-category')->shouldReturn('ArticleCategory');
    }

    function it_converts_text_to_lower_case() {
        $this->toLowerCase('ArticleCategory')->shouldReturn('articlecategory');
    }

    function it_slugifies_text() {
        $this->slugify('This will be a text for change')->shouldReturn('this-will-be-a-text-for-change');
    }
}
