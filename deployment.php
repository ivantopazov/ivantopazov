<?php
    // Режим отладки
    //
        error_reporting(-1);
        ini_set('display_errors', 1);
    //
    // Секретный ключ
    $key = 'ABCit123456';
    // Ветка - которую необходимо отслеживать
    $_branche = 'master';
    // Если репозиторий публичный, то false. Иначе делаем токен в !!НАСТРОЙКАХ ПРОФИЛЯ!!
    $_token = '55e03ffdcc98a2a1e8a85df8442363ddc17ccf8f';
    // Репозиторий в GitHub
    $_repository = 'gittitanweb/ivantopazov';
    // ----------------------------------
    // Урл АПИ
    $_url = 'https://api.github.com/repos/';
    // Название файла-памяти в корне
    $_path_filename = '.github.repo';
    // Модуль запроса
    $_get = function( $method = false, $params = [] ) use ( &$_token, &$_url, &$_repository )
    {
        // Если есть токен...
        if( $_token !== false )
        {
            $params = array_merge( $params, [
                'access_token' => $_token
            ]);
        }
        // Параметры запроса
        $get_params = ( count( $params ) > 0 ) ? '?' . http_build_query( $params ) : '';
        // Целевой урл с параметрами и подстановками
        $url = $_url . $_repository . $method . $get_params;
        // Заготовка ответа
        $data = [];
        // Получение ответа
        $json = @file_get_contents( $url, false, stream_context_create([
            //'ssl' => [
            //    "verify_peer"  => false,
            //    "verify_peer_name" => false,
            //],
            'http' => [
                'header' => 'User-Agent: Awesome-Octocat-App'
            ]
        ]));
        // Формирование ответа
        $data = ( $json ) ? json_decode( $json, true ) : false;
        // Обработка ответа
        return $data;
    };
    // Очистка полей от лишнего
    $clear_one_array = function ( $array, $is = array('title') )
    {
        $new = array();
        foreach ($array as $keys => $value)
        {
            foreach ($value as $key => $val)
            {
                if (in_array($key, $is))
                {
                    $new[$keys][$key] = $val;
                }
            }
        }
        return $new;
    };
    // Создание необходимой структуры директорий
    $renderFolders = function ( $path = false )
    {
        $arr = explode('/', $path);
        $_t = '.';
        $_p = $_t . '';
        foreach ($arr as $v)
        {
            $_p .= '/' . $v;
            if (!is_dir($_p)) {
                mkdir( $_p, 0755, true );
            }
        }
    };
    // рекурсивное удаление пустых директорий
    $reqursions_folder_rm = function( $filepath )
    {
        // Путь до файла в конечной директории
        //$filepath = 'acb/asd/asd/asd/asee/ee.txt';
        // Отбросить имя файла и получить только путь до него
        $dirname = pathinfo( $filepath )['dirname']; // -> acb/asd/asd/asd/asee
        // Из пути сформировать массив из названий папок или пустой массив
        $path = (!empty($dirname))?explode('/',$dirname):[];  // -> [ acb, asd, asd, asd, asee ]
        // Если есть хоть одна папка, которую нужно просканировать...
        if( count( $path ) > 0 )
        {
            // Прокручиваю диретории
            for ( $p=count($path); $p > 0; $p-- )
            {
                // От самой дальней до дироктории ближе к корню сайта
                $list = array_slice($path, 0, $p);
                $dir = './' . implode('/', $list);
                    // ./acb/asd/asd/asd/asee
                    // ./acb/asd/asd/asd
                    // ./acb/asd/asd
                    // ./acb/asd
                    // ./acb
                // Сканирование директории на наличие файлов и подкаталогов
                $root = scandir($dir); //  -> ./acb/asd/asd/asd/asee
                // Исключаем неимеющие практического смысла значения..
                $root = array_diff($root, array('..', '.'));
                // Если в директории ничего нет...
                if( empty( $root ) )
                {
                    // Производим удаление
                    rmdir( $dir );
                }
            }
        }
        return true;
    };
    // Получить список коммитов на наблюдаемой ветке ( Попытка хоть что то получить... )
    $commits = $_get( '/commits', [
        'sha' => $_branche
    ]);
    // Если гит не в настроении... нечего продолжать.
    if( $commits === false )
    {
        exit('Не удаётся сконнектиться с ГитХабом. Вероятно проблема в ТОКЕН.');
    }
    // Проверка секретного ключа в параметрах
    if( isset( $_GET['key'] ) && $_GET['key'] === $key )
    {
        // Блок работы с памятью
        $_last_sha = false;
        $_last_time = false;
        if( file_exists( './' . $_path_filename ))
        {
            $_repo = json_decode( file_get_contents( './' . $_path_filename ), true );
            $_last_sha = $_repo['sha'];
            $_last_time = $_repo['time'];
        }
        // Список из sha для обновления
        $updates = [];
        // Когда тупо берём крайний коммит..
        if( $_last_time == false && count( $commits ) > 0 )
        {
            $updates[] = $commits[0]['sha'];
        }
        // Когда пробираемся к крайне сохранённому коммиту через серию неудачных загрузок..
        else
        {
            $git_result = [];
            // Получить список коммитов на наблюдаемой ветке
            $commits = $_get( '/commits', [
                'sha' => $_branche,
                'since' => $_last_time
            ]);
            // Составить полный точный список коммитов
            foreach( $commits as $com )
            {
                if( $com['sha'] === $_last_sha )
                {
                    break;
                }
                $git_result[] = $com['sha'];
            }
            // Расставить список коммитов в порядке их создания
            $updates = array_reverse( $git_result );
        }
        // Файлы - которые необходимо [Создать/Изменить/Удалить]
        $files_changes = [];
        $_set_last_time = false;
        $_set_last_sha = false;
        // Если есть что обновлять ( Лишь крайнее изменение )
        if( count( $updates ) == 1 )
        {
            // Получить информацию по файлам этого коммита
            $_data = $_get( '/commits/' . $updates[0] );
            // Составить список файлов для [Создания/Изменения/Удаления]
            $files_changes = $clear_one_array( $_data['files'], [ 'sha', 'filename', 'status' ] );
            $_set_last_time = $_data['commit']['committer']['date'];
            $_set_last_sha = $updates[0];
        }
        // Если есть что обновлять
        if( count( $updates ) > 1 )
        {
            // Сравнить два коммита - вытащить обновлённые файлы
            $_data = $_get( '/compare/' . $updates[0] . '...' . end( $updates ) );
            // Составить список файлов для [Создания/Изменения/Удаления]
            $files_changes = $clear_one_array( $_data['files'], [ 'sha', 'filename', 'status' ] );
            $__commit_last = end( $_data['commits'] );
            $_set_last_time = $__commit_last['commit']['committer']['date'];
            $_set_last_sha = $__commit_last['sha'];
        }
        // Модернизация структуры проекта согласно нотации
        if( count( $files_changes ) > 0 )
        {
            foreach ( $files_changes as $git_check )
            {
                // Изменяемый файл
                $filename = './' . $git_check['filename'];
                // Название события
                $event_name = $git_check['status'];
                // Контент
                $change_content = $event_name . ' ' . $filename;
                    $change_content .= "\n\n";
                // Актуальный контент файла
                $content = $_get( '/git/blobs/' . $git_check['sha'] );
                // Удаление
                if( $git_check['status'] === 'removed' )
                {
                    // Удалить этот файл
                    unlink( $filename );
                    // Удалить структуру опустевших каталогов
                    $reqursions_folder_rm( $filename );
                }
                // Создние или Изменение
                else
                {
                    // Создать необходимую структуру деррикторий
                    $pathname = pathinfo( $filename );
                    if( !empty( $pathname ))
                    {
                        $renderFolders( $pathname['dirname'] );
                    }
                    // Запись / Перезапись в файл
                    file_put_contents( $filename, base64_decode( $content['content'] ) );
                }
            }
            // Сдвинуть крайнию версию на текущию
            file_put_contents( './' . $_path_filename, json_encode([
                'sha' => $_set_last_sha,
                'time' => $_set_last_time
            ], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            echo 'Файлов изменено в структуре: ' . count( $files_changes );
        }
        else
        {
            echo 'Структура не изменена';
        }
    }
    else
    {
        header('HTTP/1.0 403 Forbidden');
        exit('Необходим ключ доступа!');
    }
    # <-- End FILE /-->