<?php

namespace App\Ui;

use Atk4\Ui\Persistence\Ui as UiPersistence;
use Atk4\Core\Factory;
use Atk4\Ui\UserAction\ExecutorFactory;
use Atk4\Ui\{Layout, View};

class App extends \Atk4\Ui\App
{
    /**
     * @var array|false Location where to load JS/CSS files
     */
   public $cdn = [
        'atk' => 'https://cdn.jsdelivr.net/gh/atk4/ui@2.4.0/public',
        'jquery' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1',
        'serialize-object' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery-serialize-object/2.5.0',
        'semantic-ui' => 'https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.7',
        'flatpickr' => 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.6',
    ];

    /**
     * Constructor.
     *
     * @param array $defaults
     */
    public function __construct($defaults = [])
    {
        $this->setApp($this);

        // Process defaults
        if (is_string($defaults)) {
            $defaults = ['title' => $defaults];
        }

        if (isset($defaults[0])) {
            $defaults['title'] = $defaults[0];
            unset($defaults[0]);
        }

        /*
          if (is_array($defaults)) {
          throw (new Exception('Constructor requires array argument'))
          ->addMoreInfo('arg', $defaults);
          } */
        $this->setDefaults($defaults);
        /*

          foreach ($defaults as $key => $val) {
          if (is_array($val)) {
          $this->{$key} = array_merge(is_array($this->{$key} ?? null) ? $this->{$key} : [], $val);
          } elseif ($val !== null) {
          $this->{$key} = $val;
          }
          }
         */

        $this->setupTemplateDirs();

        // Set our exception handler
        if ($this->catch_exceptions) {
            set_exception_handler(\Closure::fromCallable([$this, 'caughtException']));
            set_error_handler(
                    static function (int $severity, string $msg, string $file, int $line): bool {
                        throw new \ErrorException($msg, 0, $severity, $file, $line);
                    },
                    E_ALL
            );
        }

        // Always run app on shutdown
        if ($this->always_run) {
            $this->setupAlwaysRun();
        }

        // Set up UI persistence
        if (!isset($this->ui_persistence)) {
            $this->ui_persistence = new UiPersistence();
        }

        // setting up default executor factory.
        $this->executorFactory = Factory::factory([ExecutorFactory::class]);
    }

    /**
     * Build a URL that application can use for loading HTML data.
     *
     * @param array|string $page                URL as string or array with page name as first element and other GET arguments
     * @param bool         $needRequestUri      Simply return $_SERVER['REQUEST_URI'] if needed
     * @param array        $extraRequestUriArgs additional URL arguments, deleting sticky can delete them
     *
     * @return string
     */
    public function url($page = [], $needRequestUri = false, $extraRequestUriArgs = [])
    {
        if ($needRequestUri) {
            $page = $_SERVER['REQUEST_URI'];
        }

        if ($this->page === null) {
            $requestUrl = $this->getRequestUrl();
            if (substr($requestUrl, -1, 1) === '/') {
                $this->page = 'index';
            } else {
                $this->page = basename($requestUrl, $this->url_building_ext);
            }
        }

        $pagePath = '';
        if (is_string($page)) {
            $page_arr = explode('?', $page, 2);
            $pagePath = $page_arr[0];
            parse_str($page_arr[1] ?? '', $page);
        } else {
            $pagePath = $page[0] ?? $this->page; // use current page by default
            unset($page[0]);
            //if ($pagePath) {
            //$pagePath .= $this->url_building_ext;
            //}
        }

        $args = $extraRequestUriArgs;

        // add sticky arguments
        foreach ($this->sticky_get_arguments as $k => $v) {
            if ($v && isset($_GET[$k])) {
                $args[$k] = $_GET[$k];
            } else {
                unset($args[$k]);
            }
        }

        // add arguments
        foreach ($page as $k => $v) {
            if ($v === null || $v === false) {
                unset($args[$k]);
            } else {
                $args[$k] = $v;
            }
        }

        // put URL together
        $pageQuery = http_build_query($args, '', '&', PHP_QUERY_RFC3986);
        $url = $pagePath . ($pageQuery ? '?' . $pageQuery : '');

        return $url;
    }

    public function encodeJson($data, bool $forceObject = false): string
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

        if ($forceObject) {
            $options |= JSON_FORCE_OBJECT;
        }

        $json = json_encode($data, $options | JSON_THROW_ON_ERROR, 512);

        // IMPORTANT: always convert large integers to string, otherwise numbers can be rounded by JS
        // replace large JSON integers only, do not replace anything in JSON/JS strings
        /* $json = preg_replace_callback('~"(?:[^"\\\\]+|\\\\.)*+"\K|\'(?:[^\'\\\\]+|\\\\.)*+\'\K'
          . '|(?:^|[{\[,:])[ \n\r\t]*\K-?[1-9]\d{15,}(?=[ \n\r\t]*(?:$|[}\],:]))~s', function ($matches) {
          if ($matches[0] === '' || abs((int) $matches[0]) < (2 ** 53)) {
          return $matches[0];
          }

          return '"' . $matches[0] . '"';
          }, $json); */

        return $json;
    }

    /**
     * Initializes layout.
     *
     * @param string|Layout|array $seed
     *
     * @return $this
     */
    public function initLayout($seed)
    {
        $layout = Layout::fromSeed($seed);
        $layout->setApp($this);

        if (!$this->html) {
            $this->html = new View(['defaultTemplate' => 'html.html']);
            $this->html->setApp($this);
            $this->html->invokeInit();
        }

        $this->layout = $this->html->add($layout);

        $this->initIncludes();

        return $this;
    }

    /**
     * Initialize JS and CSS includes.
     */
    public function initIncludes()
    {
        parent::initIncludes();

        $this->html->template->dangerouslyAppendHtml(
            'HEAD',
            $this->getTag('script', '$.ajaxSetup({
                headers: {
                    \'X-CSRF-TOKEN\': \'' . csrf_token() . '\'
                }
            });')
        );
    }
    
    /**
     * Will perform a preemptive output and terminate. Do not use this
     * directly, instead call it form Callback, JsCallback or similar
     * other classes.
     *
     * @param string|array $output  Array type is supported only for JSON response
     * @param string[]     $headers content-type header must be always set or consider using App::terminateHtml() or App::terminateJson() methods
     */
    public function terminate($output = '', array $headers = []): void
    {
        $headers = $this->normalizeHeaders($headers);
        if (empty($headers['content-type'])) {
            $this->response_headers = $this->normalizeHeaders($this->response_headers);
            if (empty($this->response_headers['content-type'])) {
                throw new Exception('Content type must be always set');
            }

            $headers['content-type'] = $this->response_headers['content-type'];
        }

        $type = preg_replace('~;.*~', '', strtolower($headers['content-type'])); // in LC without charset

        if ($type === 'application/json') {
            if (is_string($output)) {
                $output = $this->decodeJson($output);
            }
            $output['modals'] = $this->getRenderedModals();

            $this->outputResponseJson($output, $headers);
        } elseif (isset($_GET['__atk_tab']) && $type === 'text/html') {
            // ugly hack for TABS
            // because fomantic ui tab only deal with html and not JSON
            // we need to hack output to include app modal.
            $keys = null;
            $remove_function = '';
            foreach ($this->getRenderedModals() as $key => $modal) {
                // add modal rendering to output
                $keys[] = '#' . $key;
                $output['atkjs'] = $output['atkjs'] . ';' . $modal['js'];
                $output['html'] = $output['html'] . $modal['html'];
            }
            if ($keys) {
                $ids = implode(',', $keys);
                $remove_function = '$(\'.ui.dimmer.modals.page\').find(\'' . $ids . '\').remove();';
            }
            $output = '<script>jQuery(function() {' . $remove_function . $output['atkjs'] . '});</script>' . $output['html'];

            $this->outputResponseHtml($output, $headers);
        } elseif ($type === 'text/html') {
            $this->outputResponseHtml($output, $headers);
        } else {
            $this->outputResponse($output, $headers);
        }

        $this->run_called = true; // prevent shutdown function from triggering.
        $this->callExit();
    }
    
    /* Runs app and returns rendered template.
     */
    public function run()
    {
        ob_start();

        parent::run();

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
    
    /**
     * Output HTML response to the client.
     *
     * @param string[] $headers
     */
    private function outputResponseHtml(string $data, array $headers = []): void
    {
        $this->outputResponse(
            $data,
            array_merge($this->normalizeHeaders($headers), ['content-type' => 'text/html'])
        );
    }

    /**
     * Output JSON response to the client.
     *
     * @param string|array $data
     * @param string[]     $headers
     */
    private function outputResponseJson($data, array $headers = []): void
    {
        if (!is_string($data)) {
            $data = $this->encodeJson($data);
        }

        $this->outputResponse(
            $data,
            array_merge($this->normalizeHeaders($headers), ['content-type' => 'application/json'])
        );
    }
}