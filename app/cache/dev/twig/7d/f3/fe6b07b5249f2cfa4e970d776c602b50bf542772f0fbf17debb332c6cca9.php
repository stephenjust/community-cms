<?php

/* views/admin/base.html.twig */
class __TwigTemplate_7df3fe6b07b5249f2cfa4e970d776c602b50bf542772f0fbf17debb332c6cca9 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <title>";
        // line 4
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js\"></script>
        <script type=\"text/javascript\" src=\"/scripts/jquery.js\"></script>
        <script type=\"text/javascript\" src=\"/scripts/jquery-ui.js\"></script>
        <script type=\"text/javascript\" src=\"/scripts/jquery-cycle.js\"></script>
        <script type=\"text/javascript\" src=\"/scripts/jquery-fe.js\"></script>
        <script type=\"text/javascript\" src=\"/scripts/ajax.js\"></script>
        <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 11
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("css/admin.css"), "html", null, true);
        echo "\" />
    </head>
    <body>
        <div id=\"header\">
            <a href=\"";
        // line 15
        echo $this->env->getExtension('routing')->getUrl("admin_home");
        echo "\"><img src=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("images/admin/logo.png"), "html", null, true);
        echo "\" border=\"0px\" width=\"380px\" height=\"75px\"></a>
            <div class=\"iconbar\">
                <!-- \$ICON_BAR\$ -->
            </div>
        </div>
        <table class=\"main\">
            <tr>
                <td class=\"left\">
                    <div id=\"menu\">
                        <!-- \$NAV_BAR\$ -->
                    </div>
                </td>
                <td class=\"center\">";
        // line 27
        $this->displayBlock('body', $context, $blocks);
        echo "</td>
            </tr>\t\t
        </table>
        <div id=\"footer\">
            <!-- \$FOOTER\$ -->
        </div>
    </body>
</html>
";
    }

    // line 4
    public function block_title($context, array $blocks = array())
    {
        echo "Community CMS Administration";
    }

    // line 27
    public function block_body($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "views/admin/base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  79 => 27,  73 => 4,  60 => 27,  43 => 15,  36 => 11,  26 => 4,  21 => 1,);
    }
}
