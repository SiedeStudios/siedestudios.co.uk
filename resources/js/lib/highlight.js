import hljs from 'highlight.js/lib/core';
import lua from 'highlight.js/lib/languages/lua';
import php from 'highlight.js/lib/languages/php';
import javascript from 'highlight.js/lib/languages/javascript';
import typescript from 'highlight.js/lib/languages/typescript';
import json from 'highlight.js/lib/languages/json';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import scss from 'highlight.js/lib/languages/scss';
import sql from 'highlight.js/lib/languages/sql';
import bash from 'highlight.js/lib/languages/bash';
import python from 'highlight.js/lib/languages/python';
import yaml from 'highlight.js/lib/languages/yaml';
import ini from 'highlight.js/lib/languages/ini';
import markdown from 'highlight.js/lib/languages/markdown';
import csharp from 'highlight.js/lib/languages/csharp';
import cpp from 'highlight.js/lib/languages/cpp';
import diff from 'highlight.js/lib/languages/diff';
import plaintext from 'highlight.js/lib/languages/plaintext';

Object.entries({ lua, php, javascript, typescript, json, xml, css, scss, sql, bash, python, yaml, ini, markdown, csharp, cpp, diff, plaintext })
    .forEach(([name, lang]) => hljs.registerLanguage(name, lang));
hljs.registerAliases(['html'], { languageName: 'xml' });

export function highlight(code, language) {
    const lang = hljs.getLanguage(language) ? language : 'plaintext';
    return hljs.highlight(code, { language: lang, ignoreIllegals: true }).value;
}
