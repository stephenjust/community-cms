<?php

/* views/admin/component_log_view.html.twig */
class __TwigTemplate_95e7249bfce2078953764a84b403e651f93b3f691f98718e3a635ca0e10375d9 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<table class=\"admintable\">
    <tr>
        <th>Date</th>
        <th>Action</th>
        <th>User</th>
        <th>IP</th>
    </tr>
    ";
        // line 8
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["log_entries"]) ? $context["log_entries"] : $this->getContext($context, "log_entries")));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["entry"]) {
            // line 9
            echo "        <tr>
            <td>";
            // line 10
            echo twig_escape_filter($this->env, $this->getAttribute($context["entry"], "getDate", array(), "method"), "html", null, true);
            echo "</td>
            <td>";
            // line 11
            echo twig_escape_filter($this->env, $this->getAttribute($context["entry"], "getAction", array(), "method"), "html", null, true);
            echo "</td>
            <td>";
            // line 12
            echo twig_escape_filter($this->env, $this->getAttribute($context["entry"], "getUser", array(), "method"), "html", null, true);
            echo "</td>
            <td>";
            // line 13
            echo twig_escape_filter($this->env, $this->getAttribute($context["entry"], "getIP", array(), "method"), "html", null, true);
            echo "</td>
        </tr>
    ";
            $context['_iterated'] = true;
        }
        if (!$context['_iterated']) {
            // line 16
            echo "        <tr>
            <td colspan=\"4\">No entries to display.</td>
        </tr>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['entry'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 20
        echo "</table>
";
    }

    public function getTemplateName()
    {
        return "views/admin/component_log_view.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  65 => 20,  56 => 16,  48 => 13,  44 => 12,  40 => 11,  36 => 10,  33 => 9,  28 => 8,  19 => 1,);
    }
}
