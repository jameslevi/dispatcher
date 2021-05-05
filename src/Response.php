<?php

namespace Graphite\Component\Dispatcher;

class Response
{
    /**
     * Dispatcher instance context.
     * 
     * @var \Graphite\Component\Dispatcher\Dispatcher
     */
    private $context;

    /**
     * Body of the response message.
     * 
     * @var string
     */
    private $body;

    /**
     * Construct a new response object.
     * 
     * @param   \Graphite\Component\Dispatcher\Dispatcher $context
     * @return  void
     */
    public function __construct(Dispatcher $context)
    {
        $this->context = $context;
    }

    /**
     * Redirect to a new route.
     * 
     * @param   string $location
     * @return  $this
     */
    public function redirect(string $location)
    {
        $this->context->redirect($location);

        return $this;
    }

    /**
     * Set http headers.
     * 
     * @param   string $key
     * @param   string $content
     * @return  $this
     */
    public function setHeader(string $key, string $content)
    {
        $this->context->setHeader($key, $content);

        return $this;
    }

    /**
     * Abort request and return http status.
     * 
     * @param   int $code
     * @return  $this
     */
    public function abort(int $code)
    {
        $this->context->abort($code);

        return $this;
    }

    /**
     * Return the length of the response message.
     * 
     * @return  int
     */
    public function contentLength()
    {
        return strlen($this->body);
    }

    /**
     * Set the response message body.
     * 
     * @param   string $message
     * @return  $this
     */
    public function send(string $message)
    {
        if(is_null($this->body))
        {
            $this->body = $message;
        }

        return $this;
    }

    /**
     * Set the response message body as JSON.
     * 
     * @param   array $data
     * @return  $this
     */
    public function sendJson(array $data)
    {
        return $this->send(json_encode($data))
                    ->setHeader('Content-Type', 'application/json');
    }

    /**
     * Return response message body.
     * 
     * @return  string
     */
    public function getBody()
    {
        return $this->body;
    }
}