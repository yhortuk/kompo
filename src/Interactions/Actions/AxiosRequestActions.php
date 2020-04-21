<?php

namespace Kompo\Interactions\Actions;

use Kompo\Routing\RouteFinder;

trait AxiosRequestActions
{
    protected function prepareAxiosRequest($data)
    {  
        return $this->prepareAction('axiosRequest', array_merge($data, [
            'sessionTimeoutMessage' => __('sessionTimeoutMessage')
        ])); 
    }


    public function selfHttpRequest($requestType, $kompoAction, $methodName, $ajaxPayload = null)
    {
        return $this->prepareAxiosRequest([
            'route' => RouteFinder::getKompoRoute(),
            'routeMethod' => $requestType,
            'kompoAction' => $kompoAction,
            'kompoMethod' => $methodName,
            'ajaxPayload' => $ajaxPayload
        ]);
    }

    /**
     * Loads a view through AJAX. 
     * To display the view in a container, you may chain it with the methods `inModal` or `inPanel`. For example: 
     * <php>->loadView('route-of-view')->inModal()</php>
     *
     * @param      string  $methodName    The method name that will return a view or HTML response.
     * @param      array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     *
     * @return     self   
     */
    public function getKomposer($komposerClass, $ajaxPayload = null)
    {
        return $this->prepareAxiosRequest([
            'route' => RouteFinder::getKompoRoute(),
            'routeMethod' => 'GET',
            'komposerClass' => $komposerClass,
            'ajaxPayload' => $ajaxPayload
        ]);
    }

    /**
     * Loads a view through AJAX. 
     * To display the view in a container, you may chain it with the methods `inModal` or `inPanel`. For example: 
     * <php>->loadView('route-of-view')->inModal()</php>
     *
     * @param      string  $methodName    The method name that will return a view or HTML response.
     * @param      array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     *
     * @return     self   
     */
    public function getView($methodName, $ajaxPayload = null)
    {
        return $this->selfHttpRequest('POST', 'get-view', $methodName, $ajaxPayload);
    }

    /**
     * Includes additional komponents from the server, which will be included in the Form data.
     * To display it, you should chain it with the methods `inModal` or `inPanel`, the containers in which the view will be displayed. For example:
     * <php>->getKomponents('newKomponentsMethod')->inPanel('panel-id')</php>
     * 
     *
     * @param  string  $methodName    The class's method name that will return the new komponents.
     * @param  array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     * 
     * @return self
     */
    public function getKomponents($methodName, $ajaxPayload = null)
    {
        $this->applyToElement(function($element) use($methodName) {
            $element->data([ 'includes' => $methodName ]);
        });

        return $this->selfHttpRequest('POST', 'include-komponents', $methodName, $ajaxPayload)
            ->data([ 'included' => true ]); //to tell Panel to include rather than separate form
    }


    /**
     * Loads a view with a GET ajax request. 
     * To display the view in a container, you may chain it with the methods `inModal` or `inPanel`. For example: 
     * <php>->getView('get-route-of-view')->inModal()</php>
     *
     * @param  string  $methodName    The class's method name that will return the new komponents.
     * @param  array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     *
     * @return     self   
     */
    public function getSelf($methodName, $ajaxPayload = null)
    {
        return $this->selfHttpRequest('GET', 'self-method', $methodName, $ajaxPayload);
    }

    /**
     * Loads a view with a POST ajax request. 
     * To display the view in a container, you may chain it with the methods `inModal` or `inPanel`. For example: 
     * <php>->postView('get-route-of-view')->inModal()</php>
     *
     * @param  string  $methodName    The class's method name that will return the new komponents.
     * @param  array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     *
     * @return     self   
     */
    public function postSelf($methodName, $ajaxPayload = null)
    {
        return $this->selfHttpRequest('POST', 'self-method', $methodName, $ajaxPayload);
    }

    /**
     * Loads a view with a POST ajax request. 
     * To display the view in a container, you may chain it with the methods `inModal` or `inPanel`. For example: 
     * <php>->postView('get-route-of-view')->inModal()</php>
     *
     * @param  string  $methodName    The class's method name that will return the new komponents.
     * @param  array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     *
     * @return     self   
     */
    public function putSelf($methodName, $ajaxPayload = null)
    {
        return $this->selfHttpRequest('PUT', 'self-method', $methodName, $ajaxPayload);
    }

    /**
     * Loads a view with a POST ajax request. 
     * To display the view in a container, you may chain it with the methods `inModal` or `inPanel`. For example: 
     * <php>->postView('get-route-of-view')->inModal()</php>
     *
     * @param  string  $methodName    The class's method name that will return the new komponents.
     * @param  array|null  $ajaxPayload  Additional custom data to add to the request (optional).
     *
     * @return     self   
     */
    public function deleteSelf($methodName, $ajaxPayload = null)
    {
        return $this->selfHttpRequest('DELETE', 'self-method', $methodName, $ajaxPayload);
    }
    
    
}