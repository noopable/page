<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
return array(
    //'name' => 'samplePage',
    //'invokable' => 'Page\Pages\Sample',
    'class' => 'Page\Pages\Sample',
    'default_controller' => 'page',
    'default_action' => 'index',
    'properties' => array(
        'title' => 'トップページ',
        'pane' => array(
            //'classes' => 'container',
            'tag' => '',//cancel root level auto div
            'inner' => array(
                array(
                'id' => 'overview',
                //'tag' => 'header',
                'order' => 100,
                'classes' => array('container','jumbotron', 'subhead', 'header',/*'cBoth', 'row'*/),
                //'inner' => array(
                //    array(
                //        'classes' => 'container',
                        'var' => 'header',
                //    ),
                //)
                ),
                array(
                    'classes' => 'container',
                    'var' => 'content',
                ),
                array(
                    'id' => 'footer',
                    'tag' => 'footer',
                    'inner' => array(
                        'classes' => 'container',
                        'var' => 'footer',
                    ),
                ),
            ),
        ),
    ),
    'options' => array(
        'blockBuilder' => function ($b) {
            $b->insert('blocks/navBar',
                 [
                    //'prototype' => $service->getBlock('NavBar'),
                    'properties' => 
                        ['active' => 'home',],
                 ]
            );

            $b('blocks/topRow',
                array(
                    'order' => 10,
                )
            );


            $b->block('header',
                 [
                    'options' => 
                    [
                        'template'=>'pages/widget/header',
                        'captureTo' => 'header',
                        //'viewModelAppend' => true,
                    ],
                    'order' => 100,
                ]
            );

            $b->block('carousel',
                 array(
                    'options' => array(
                        'template'=>'pages/widget/carousel',
                        //'captureTo' => 'content',
                        'viewModelAppend' => true,
                    ),
                    'order' => 80,
                )
            );

            $b->block('footer',
                 array(
                    'options' => array(
                        'template'=>'pages/widget/footer',
                        'captureTo' => 'footer',
                        //'viewModelAppend' => true,
                    ),
                    'order' => 80,
                )
            );
        },
    ),
);