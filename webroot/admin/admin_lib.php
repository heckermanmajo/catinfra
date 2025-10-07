<?php

    final class admin_lib
    {
        private function __construct() {}

        public static function main_admin_nav(): void
        {
            ?>
            <header>
                <nav>
                    <a href="/admin/"> Start Page </a>
                    &nbsp; | &nbsp;
                    <a href="/admin/users.php"> Users </a>
                    &nbsp; | &nbsp;
                    <a href="/admin/communities.php"> Communities </a>
                    &nbsp; | &nbsp;
                    <a href="/admin/eventlogs.php"> Eventlogs </a>
                    &nbsp; | &nbsp;
                </nav>
            </header>
            <?php
        }

        static function render_collapsible_list(array $data, string $rootVar = '$data'): string
        {
            $isAssoc = fn($arr) => array_keys($arr) !== range(0, count($arr) - 1);
            $esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $escAttr = $esc;

            $fmtKeyForPhp = function($key): string {
                if (is_int($key)) return '[' . $key . ']';
                $k = addcslashes((string)$key, "\\\"");
                return '["' . $k . '"]';
            };

            $buildPhpPath = function(array $pathParts, string $rootVar) use ($fmtKeyForPhp): string {
                $suffix = '';
                foreach ($pathParts as $part) $suffix .= $fmtKeyForPhp($part);
                return $rootVar . $suffix;
            };

            $render = function ($node, array $pathParts = []) use (&$render, $isAssoc, $esc, $escAttr, $buildPhpPath, $rootVar): string
            {
                $listTag = $isAssoc($node) ? 'ul' : 'ol';
                $html = "<{$listTag} class=\"tree-list\">\n";

                foreach ($node as $key => $value)
                {
                    $label = is_int($key) ? "#{$key}" : (string)$key;
                    $currentPath = array_merge($pathParts, [$key]);
                    $phpPath = $buildPhpPath($currentPath, $rootVar);
                    $phpPathAttr = $escAttr($phpPath);
                    $depth = count($currentPath);
                    $collapsed = ($depth % 3) === 0;
                    $liClass = $collapsed ? ' class="collapsed"' : '';

                    $html .= "  <li{$liClass}>";

                    if (is_array($value))
                    {
                        $count = count($value);
                        $type = ($isAssoc($value) ? 'object' : 'array') . "({$count})";

                        $html .= "<button type=\"button\" class=\"toggle\" aria-expanded=\"" . ($collapsed ? "false" : "true") . "\" aria-label=\"toggle {$esc($label)}\"></button>";
                        $html .= "<span class=\"key\">{$esc($label)}</span>";
                        $html .= " <button type=\"button\" class=\"copy\" data-copy=\"{$phpPathAttr}\" title=\"PHP-Pfad kopieren ({$esc($phpPath)})\" aria-label=\"PHP-Pfad kopieren\">📋</button>";
                        $html .= " <span class=\"meta\">{$esc($type)}</span>";

                        $html .= "\n    <div class=\"child-wrapper\">\n";
                        $html .= $render($value, $currentPath);
                        $html .= "    </div>\n";
                    }
                    else
                    {
                        if ($value === null)
                        {
                            $valStr = '<span class="null">null</span>';
                            $type = 'NULL';
                        }
                        elseif (is_bool($value))
                        {
                            $valStr = '<span class="bool">' . ($value ? 'true' : 'false') . '</span>';
                            $type = 'bool';
                        }
                        elseif (is_string($value))
                        {
                            $valStr = '<span class="string">"' . $esc($value) . '"</span>';
                            $type = 'string';
                        }
                        else
                        {
                            $valStr = '<span class="number">' . $esc($value) . '</span>';
                            $type = gettype($value);
                        }

                        $html .= "<span class=\"key\">{$esc($label)}</span>";
                        $html .= " <button type=\"button\" class=\"copy\" data-copy=\"{$phpPathAttr}\" title=\"PHP-Pfad kopieren ({$esc($phpPath)})\" aria-label=\"PHP-Pfad kopieren\">📋</button>";
                        $html .= ": {$valStr} <span class=\"meta\">{$esc($type)}</span>";
                    }

                    $html .= "</li>\n";
                }

                $html .= "</{$listTag}>\n";
                return $html;
            };

            $uid = 'json-tree-' . bin2hex(random_bytes(4));
            $out = "<div class=\"json-tree\" id=\"{$uid}\" data-json-tree>\n";
            $out .= $render($data);
            $out .= <<<HTML
</div>
<style>
.child-wrapper { margin: 0; line-height: 0.0; }
.tree-list { margin: 0; padding: 0; list-style: none; line-height: 0.0; }
#{$uid}.json-tree { font: 13px/1.25 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
#{$uid} .tree-list { margin: 0; padding-left: 1rem; list-style-position: inside; }
#{$uid} ul.tree-list { list-style: disc; }
#{$uid} ol.tree-list { list-style: decimal; }
#{$uid} li { margin: 0; padding: 0; line-height: 1.0; }
#{$uid} .key { font-weight: 600; margin-right: .25rem; }
#{$uid} .meta { color: #666; font-size: .85em; margin-left: .25rem; }
#{$uid} .string { color: #0a7; }
#{$uid} .number { color: #07a; }
#{$uid} .bool { color: #a70; }
#{$uid} .null { color: #999; font-style: italic; }
#{$uid} .toggle {
  border: 0; background: transparent; width: 1rem; margin-right: .2rem;
  cursor: pointer; vertical-align: text-bottom; padding: 0;
}
#{$uid} .toggle::before {
  content: "▾"; display: inline-block; transform-origin: center; transition: transform .15s ease;
}
#{$uid} li.collapsed > .toggle::before { transform: rotate(-90deg); }
#{$uid} .child-wrapper { margin-left: .8rem; display: block; }
#{$uid} li.collapsed > .child-wrapper { display: none; }
#{$uid} .copy {
  border: 0;
  background: transparent;
  cursor: pointer;
  padding: 0 .1rem;
  margin-left: .15rem;
  font-size: 0.95em;
  line-height: 1;
  opacity: .8;
}
#{$uid} .copy:hover { opacity: 1; }
#{$uid} .copy:focus { outline: 1px dashed #aaa; outline-offset: 2px; }
</style>

<script>
(() => {
  const root = document.getElementById("{$uid}");
  if (!root) return;

  root.addEventListener("click", async (e) => {
    const btnToggle = e.target.closest(".toggle");
    if (btnToggle && root.contains(btnToggle)) {
      const li = btnToggle.parentElement;
      const child = li.querySelector(":scope > .child-wrapper");
      if (!child) return;
      const wasOpen = btnToggle.getAttribute("aria-expanded") === "true";
      const willOpen = !wasOpen;
      btnToggle.setAttribute("aria-expanded", String(willOpen));
      li.classList.toggle("collapsed", !willOpen);
      return;
    }

    const btnCopy = e.target.closest(".copy");
    if (btnCopy && root.contains(btnCopy)) {
      e.preventDefault();
      e.stopPropagation();
      const text = btnCopy.getAttribute("data-copy") || "";
      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(text);
        } else {
          const ta = document.createElement("textarea");
          ta.value = text;
          ta.style.position = "absolute";
          ta.style.left = "-9999px";
          document.body.appendChild(ta);
          ta.select();
          document.execCommand("copy");
          ta.remove();
        }
        const old = btnCopy.textContent;
        btnCopy.textContent = "✅";
        setTimeout(() => btnCopy.textContent = old || "📋", 700);
      } catch {
        const old = btnCopy.textContent;
        btnCopy.textContent = "❌";
        setTimeout(() => btnCopy.textContent = old || "📋", 900);
      }
    }
  });
})();
</script>
HTML;

            return $out;
        }
    }
