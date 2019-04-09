<?php

use kallbuloso\BladeDirectives\DirectivesRepository;

return [

    /*
    |---------------------------------------------------------------------
    | @truncate
    |---------------------------------------------------------------------
    */
   'truncate' => function ($expression) {
        list($string, $length) = explode(',',str_replace(['(',')',' '], '', $expression));

        $trunc = "strlen({$string}) > {$length} ? substr({$string},0,{$length}).'...' : {$string}";
        return '{!! '. $trunc .' !!}';
    },
    /*
    |---------------------------------------------------------------------
    | @istrue / @isfalse
    |---------------------------------------------------------------------
    */

    'istrue' => function ($expression) {
        if (str_contains($expression, ',')) {
            $expression = DirectivesRepository::parseMultipleArgs($expression);

            return  "<?php if (isset({$expression->get(0)}) && (bool) {$expression->get(0)} === true) : ?>".
                    "<?php echo {$expression->get(1)}; ?>".
                    '<?php endif; ?>';
        }

        return "<?php if (isset({$expression}) && (bool) {$expression} === true) : ?>";
    },

    'endistrue' => function ($expression) {
        return '<?php endif; ?>';
    },

    'isfalse' => function ($expression) {
        if (str_contains($expression, ',')) {
            $expression = DirectivesRepository::parseMultipleArgs($expression);

            return  "<?php if (isset({$expression->get(0)}) && (bool) {$expression->get(0)} === false) : ?>".
                    "<?php echo {$expression->get(1)}; ?>".
                    '<?php endif; ?>';
        }

        return "<?php if (isset({$expression}) && (bool) {$expression} === false) : ?>";
    },

    'endisfalse' => function ($expression) {
        return '<?php endif; ?>';
    },

    /*
    |---------------------------------------------------------------------
    | @isnull / @isnotnull
    |---------------------------------------------------------------------
    */

    'isnull' => function ($expression) {
        return "<?php if (is_null({$expression})) : ?>";
    },

    'endisnull' => function ($expression) {
        return '<?php endif; ?>';
    },

    'isnotnull' => function ($expression) {
        return "<?php if (! is_null({$expression})) : ?>";
    },

    'endisnotnull' => function ($expression) {
        return '<?php endif; ?>';
    },

    /*
    |---------------------------------------------------------------------
    | @mix
    |---------------------------------------------------------------------
    */

    'mix' => function ($expression) {
        if (ends_with($expression, ".css'")) {
            return '<link rel="stylesheet" href="<?php echo mix('.$expression.') ?>">';
        }

        if (ends_with($expression, ".js'")) {
            return '<script src="<?php echo mix('.$expression.') ?>"></script>';
        }

        return "<?php echo mix({$expression}); ?>";
    },

    /*
    |---------------------------------------------------------------------
    | @style
    |---------------------------------------------------------------------
    */

    'style' => function ($expression) {
        if (! empty($expression)) {
            return '<link rel="stylesheet" href="'.DirectivesRepository::stripQuotes($expression).'">';
        }

        return '<style>';
    },

    'endstyle' => function () {
        return '</style>';
    },

    /*
    |---------------------------------------------------------------------
    | @script
    |---------------------------------------------------------------------
    */

    'script' => function ($expression) {
        if (! empty($expression)) {
            return '<script src="'.DirectivesRepository::stripQuotes($expression).'"></script>';
        }

        return '<script>';
    },

    'endscript' => function () {
        return '</script>';
    },    

    /**
     * @asset
     */
    'asset' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        $variable = DirectivesRepository::stripQuotes($expression->get(0));
        $include = DirectivesRepository::stripQuotes($expression->get(1));
        
        if (ends_with($variable, ".css")) {
            if (! empty($include)){
                return '<link rel="stylesheet" id="'.$include.'" href="{{ asset(\''.$variable.'\') }}">';
            }
            return '<link rel="stylesheet" href="{{ asset(\''.$variable.'\') }}">';
        }

        if (ends_with($variable, ".js")) {
            return '<script src="{{ asset(\''.$variable.'\') }}"></script>';
        }
    },


    /*
    |---------------------------------------------------------------------
    | @js
    |---------------------------------------------------------------------
    */

    'js' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        $variable = DirectivesRepository::stripQuotes($expression->get(0));

        return  "<script>\n".
                "window.{$variable} = <?php echo is_array({$expression->get(1)}) ? json_encode({$expression->get(1)}) : '\''.{$expression->get(1)}.'\''; ?>;\n".
                '</script>';
    },

    /*
    |---------------------------------------------------------------------
    | @inline
    |---------------------------------------------------------------------
    */

    'inline' => function ($expression) {
        $include = "/* {$expression} */\n".
                   "<?php include public_path({$expression}) ?>\n";

        if (ends_with($expression, ".html'")) {
            return $include;
        }

        if (ends_with($expression, ".css'")) {
            return "<style>\n".$include.'</style>';
        }

        if (ends_with($expression, ".js'")) {
            return "<script>\n".$include.'</script>';
        }
    },

    /*
    |---------------------------------------------------------------------
    | @routeis
    |---------------------------------------------------------------------
    */

    'routeis' => function ($expression) {
        return "<?php if (fnmatch({$expression}, Route::currentRouteName())) : ?>";
    },

    'endrouteis' => function ($expression) {
        return '<?php endif; ?>';
    },

    'routeisnot' => function ($expression) {
        return "<?php if (! fnmatch({$expression}, Route::currentRouteName())) : ?>";
    },

    'endrouteisnot' => function ($expression) {
        return '<?php endif; ?>';
    },

    /*
    |---------------------------------------------------------------------
    | @instanceof
    |---------------------------------------------------------------------
    */

    'instanceof' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return  "<?php if ({$expression->get(0)} instanceof {$expression->get(1)}) : ?>";
    },

    'endinstanceof' => function () {
        return '<?php endif; ?>';
    },

    /*
    |---------------------------------------------------------------------
    | @typeof
    |---------------------------------------------------------------------
    */

    'typeof' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return  "<?php if (gettype({$expression->get(0)}) == {$expression->get(1)}) : ?>";
    },

    'endtypeof' => function () {
        return '<?php endif; ?>';
    },

    /*
    |---------------------------------------------------------------------
    | @dump, @dd
    |---------------------------------------------------------------------
    */

    'dump' => function ($expression) {
        return "<?php dump({$expression}); ?>";
    },

    'dd' => function ($expression) {
        return "<?php dd({$expression}); ?>";
    },

    /*
    |---------------------------------------------------------------------
    | @pushonce
    |---------------------------------------------------------------------
    */

    'pushonce' => function ($expression) {
        list($pushName, $pushSub) = explode(':', trim(substr($expression, 1, -1)));

        $key = '__pushonce_'.str_replace('-', '_', $pushName).'_'.str_replace('-', '_', $pushSub);

        return "<?php if(! isset(\$__env->{$key})): \$__env->{$key} = 1; \$__env->startPush('{$pushName}'); ?>";
    },

    'endpushonce' => function () {
        return '<?php $__env->stopPush(); endif; ?>';
    },

    /*
    |---------------------------------------------------------------------
    | @repeat
    |---------------------------------------------------------------------
    */

    'repeat' => function ($expression) {
        return "<?php for (\$iteration = 0 ; \$iteration < (int) {$expression}; \$iteration++): ?>";
    },

    'endrepeat' => function ($expression) {
        return '<?php endfor; ?>';
    },

    /*
     |---------------------------------------------------------------------
     | @data
     |---------------------------------------------------------------------
     */

    'data' => function ($expression) {
        $output = 'collect((array) '.$expression.')
            ->map(function($value, $key) {
                return "data-{$key}=\"{$value}\"";
            })
            ->implode(" ")';

        return "<?php echo $output; ?>";
    },

    /*
    |---------------------------------------------------------------------
    | @iconsi, @iconfa, @iconfas, @iconfar, @iconfal, @iconfab, @iconmdi, @iconglyph
    |---------------------------------------------------------------------
    */

    'iconsi' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="si si-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconfa' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="fa fa-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconfas' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="fas fa-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconfar' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="far fa-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconfal' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="fal fa-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconfab' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="fab fa-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconmdi' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="mdi mdi-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    'iconglyph' => function ($expression) {
        $expression = DirectivesRepository::parseMultipleArgs($expression);

        return '<i class="glyphicons glyphicons-'.DirectivesRepository::stripQuotes($expression->get(0)).' '.DirectivesRepository::stripQuotes($expression->get(1)).'"></i>';
    },

    /*
    |---------------------------------------------------------------------
    | @haserror
    |---------------------------------------------------------------------
    */

    'haserror' => function ($expression) {
        return '<?php if (isset($errors) && $errors->has('.$expression.')): ?>';
    },

    'endhaserror' => function () {
        return '<?php endif; ?>';
    },

    /*
    |-----------------------------------------------------------------------
    | Format date
    | @formatdate('2018-05-28','d/m/Y')
    |-----------------------------------------------------------------------
    */
    'formatDate' => function ($expression)
    {
        list($date, $format) = explode(',', $expression);
        return '<?php echo date_format(date_create($date), $format); ?>';
    },

    /*
    |-----------------------------------------------------------------------
    | FormOpen
    | @formopen([Attributes])
    |-----------------------------------------------------------------------
    */
    'formOpen' => function ($expression)
    {
        return '{!! Form::open(['.$expression.']) !!}';
    },

    /*
    |-----------------------------------------------------------------------
    | FormClose
    | @formopen([Attributes])
    |-----------------------------------------------------------------------
    */
    'formClose' => function ()
    {
        return '{!! Form::close() !!}';
    },

    /*
    |---------------------------------------------------------------------
    | @cpf
    |---------------------------------------------------------------------
    */
   'cpf' => function ($expression) {
        $expression = DirectivesRepository::stripSpaces($expression);

        return "<?php if ($expression) { echo substr($expression, 0, 3) . '.' . substr($expression, 3, 3) . '.' .substr($expression, 6, 3) . '-' . substr($expression, 9, 2); } ?>";
    },

    /*
    |---------------------------------------------------------------------
    | @cnpj
    |---------------------------------------------------------------------
    */
    'cnpj' => function ($expression) {
        $expression = DirectivesRepository::stripSpaces($expression);

        return "<?php if ($expression) { echo substr($expression, 0, 2) . '.' . substr($expression, 2, 3) . '.' . substr($expression, 5, 3) . '/' . substr($expression, 8, 4) . '-' . substr($expression, 12, 2); } ?>";
    },

    /*
    |---------------------------------------------------------------------
    | @route('routeName')
    |---------------------------------------------------------------------
    */
    'route' => function ($expression) {
        return "<?php echo route($expression); ?>";    
    },

    /*
    |---------------------------------------------------------------------
    | @activeIfUrl('UrlName','active')
    | @activeIfUrl('UrlName','open')
    |---------------------------------------------------------------------
    */
    'activeIfUrl' => function ($expression) {
        list($url, $class) = explode(',',str_replace(['(',')',' '], '', $expression));

        $activeUrl = "<?php echo e(request()->is({$url}) ? $class : ''); ?>";
        return $activeUrl;
    },

    /*
    |---------------------------------------------------------------------
    | @telefone
    |---------------------------------------------------------------------
    */
    'telefone' => function ($expression) {
        $expression = DirectivesRepository::stripSpaces($expression);

        if (strlen($expression) === 10) {
            // Telefone Fixo / Celular (8 dígitos)
            return "<?php if ($expression) { echo '(' . substr($expression, 0, 2) . ') ' . substr($expression, 2, 4) . '-' . substr($expression, 6); } ?>";
        } else if (strlen($expression) === 11) {
            // Telefone Celular com 9º Dígito
            return "<?php if ($expression) { echo '(' . substr($expression, 0, 2) . ') ' . substr($expression, 2, 1) . '-' . substr($expression, 3, 4) . '-' . substr($expression, 7); } ?>";
        } else if (strlen($expression) > 11) {
            // Telefone com Ramal
            return "<?php if ($expression) { echo '(' . substr($expression, 0, 2) . ') ' . substr($expression, 2, 1 . '-' . substr($expression, 3, 4) . '-' . substr($expression, 7, 4) . '-' . substr($expression, 11); } ?>";
        } else {
            return "<?php if ($expression) { echo $expression; } ?>";
        }
    },

    /*
    |---------------------------------------------------------------------
    | @dinheiro
    |---------------------------------------------------------------------
    */
    'dinheiro' => function ($expression) {
        $expression = DirectivesRepository::stripSpaces($expression);

        return "<?php if ($expression) { echo 'R$ ' . number_format($expression, 2, ',', '.'); } ?>";
    },

    /*
    |---------------------------------------------------------------------
    | @cep
    |---------------------------------------------------------------------
    */
    'cep' => function ($expression) {
        $expression = DirectivesRepository::stripSpaces($expression);

        /* return "<?php if ($expression) { echo substr($expression, 0, 5) . '.' . substr($expression, 2, 3) . '-' . substr($expression, 5, 3); } ?>"; */
        return "<?php if ($expression) { echo substr($expression, 0, 5) . '-' . substr($expression, 5, 3); } ?>";
    },

];
