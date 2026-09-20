<?php

if (! function_exists('tooltip_attrs')) {
    /**
     * HTML attributes that attach the shared admin tooltip to an element,
     * for a static label.
     *
     * Echo unescaped in Blade: {!! tooltip_attrs('Projects') !!}
     */
    function tooltip_attrs(string $label, string $position = 'right'): string
    {
        return tooltip_attrs_expr("'".addslashes($label)."'", $position);
    }
}

if (! function_exists('tooltip_attrs_expr')) {
    /**
     * Same as tooltip_attrs(), but the label is a raw Alpine expression that is
     * re-evaluated on every hover/focus, e.g. for state-dependent labels:
     *
     * {!! tooltip_attrs_expr("darkMode ? 'Switch to light mode' : 'Switch to dark mode'", 'below') !!}
     *
     * $position: 'right' (collapsed sidebar rail, hidden while expanded) or
     *            'below' (centered under the element, e.g. header buttons).
     */
    function tooltip_attrs_expr(string $labelExpression, string $position = 'right'): string
    {
        $args = '$el, '.$labelExpression.($position === 'below' ? ", 'below'" : '');
        $show = htmlspecialchars("showTip({$args})", ENT_QUOTES, 'UTF-8');

        return "@mouseover=\"{$show}\" @focus=\"{$show}\" @mouseleave=\"hideTip()\" @blur=\"hideTip()\"";
    }
}
