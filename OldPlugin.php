<?php
/**
 * Plugin Name: Scae Plugin For Code
 */


// function get_value_from_enum($string)
// {
//     $enumName = 'KolbQuestions::' . strtoupper($string);

//     if (defined($enumName)) {
//         return constant($enumName)->value;
//     }

//     return 0;
// }

function my_custom_ajaxurl() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}
add_action('wp_head', 'my_custom_ajaxurl');


function custom_dashboard_shortcode()
{
    global $wpdb;
    if (!is_user_logged_in()) {
        return 'Por favor, faça login para acessar o dashboard.';
    }

    $user = wp_get_current_user();
    $is_professor = in_array('professor', (array) $user->roles);
    $is_admin = in_array('administrator', (array) $user->roles);
    $user_id = $user->ID;

    if (isset($_POST['create_link']) && !empty($_POST['class_name']) && !empty($_POST['class_type'])) {
        $class_name = sanitize_text_field($_POST['class_name']);
        $class_type = sanitize_text_field($_POST['class_type']);
        $randomkey = time();

        // Definir o prefixo com base no tipo de classe
        $prefix = '';
        switch ($class_type) {
            case '1-4':
                $prefix = '1d';
                break;
            case '5-9':
                $prefix = '2d';
                break;
            case 'professor':
                $prefix = '3d';
                break;
        }

        $existing_post = get_posts(array(
            'post_type' => 'scae-class',
            'title' => $class_name,
            'author' => $user_id,
            'post_status' => 'publish',
            'numberposts' => 1
        ));

        if ($existing_post) {
            echo '<div class="scae-notice scae-error">Falha ao criar classe. Essa classe já existe.</div>';
        } else {
            $new_post = array(
                'post_title' => $class_name,
                'post_status' => 'publish',
                'post_author' => $user_id,
                'post_type' => 'scae-class',
                'guid' => 'https://g.scae.academy/mapa-cognitivo-e-experiencial-scae/?formcode=' . $prefix . $randomkey
            );

            $post_id = wp_insert_post($new_post);

            if ($post_id) {
                echo '<div class="scae-notice">Classe criada com sucesso!</div>';
            } else {
                echo '<div class="scae-notice scae-error">Falha ao criar classe. Por favor tente novamente.</div>';
            }
        }
        $_POST = array();
    }

    ob_start();
    ?>
    <div id="classModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Criar Nova Classe</h2>
            <br>
            <form method="post" action="">
                <div class="scae-form-group">
                    <label for="class_name">Classe:</label>
                    <input type="text" id="class_name" name="class_name" required>
                </div>
                <br>
                <div class="scae-form-group">
                    <label for="class_type">Tipo de Classe:</label>
                    <select id="class_type" name="class_type" required>
                        <option value="1-4">1º a 4º Série</option>
                        <option value="5-9">5º a 9º Série</option>
                        <option value="professor">Para Professores</option>
                    </select>
                </div>
                <br>
                <div class="scae-form-group">
                    <button type="submit" name="create_link" class="class-button">Criar Classe</button>
                </div>
            </form>
        </div>
    </div>
    <div class="custom-dashboard">
        <div class="sidebar">
            <ul>
                <!-- <li><a href="#profile">Perfil</a></li> -->

                <?php if ($is_professor || $is_admin): ?>
                    <li><a href="#my-classes">Minhas Classes</a></li>
                    <li><a href="#my-entries">Minhas Entradas</a></li>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <li><a href="#all-classes">Todas as Classes</a></li>
                    <li><a href="#all-entries">Todas as Entradas</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="content">
            <!-- <div id="profile" class="tab-content">
                <h2>Perfil</h2>
                <p>Informações do seu perfil.</p>
            </div> -->
            <?php
            if ($is_professor || $is_admin):
                $args = array(
                    'post_type' => 'scae-class',
                    'author' => $user->ID,
                    'posts_per_page' => -1,
                );

                $classes = new WP_Query($args);
                ?>
                <div id="my-classes" class="tab-content">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>Minhas Classes</h2>
                        <button id="openModal" class="class-button">Criar Classe</button>
                    </div>
                    <?php
                    if ($classes->have_posts()):
                        echo '<ul>';
                        while ($classes->have_posts()):
                            $classes->the_post();
                            $form_url = get_the_guid();
                            echo '<li>';
                            echo '<strong>' . get_the_title() . '</strong> - ';
                            echo '<a href="' . esc_url($form_url) . '" target="_blank">Acessar Formulário</a>';
                            echo '</li>';
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else:
                        echo '<p>Nenhuma classe encontrada.</p>';
                    endif;
                    ?>
                </div>

                <div id="my-entries" class="tab-content">
                    <h2>Minhas Entradas</h2>

                    <?php
                    if ($classes->have_posts()):
                        echo '<ul>';
                        while ($classes->have_posts()):
                            $classes->the_post();
                            $form_code = explode('formcode=', get_the_guid())[1];
                            $url = 'https://g.scae.academy/listar-entradas/?formcode=' . $form_code;
                            echo '<li>';
                            echo '<strong>' . get_the_title() . '</strong> - ';
                            echo '<a href="' . esc_url($url) . '" target="_blank">Acessar Entradas</a>';
                            echo '</li>';
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else:
                        echo '<p>Nenhuma classe encontrada.</p>';
                    endif;
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <div id="all-classes" class="tab-content">
                    <h2>Todas as Classes</h2>
                    <?php
                    $args = array(
                        'post_type' => 'scae-class',
                        'posts_per_page' => -1,
                    );
                    $classes = new WP_Query($args);

                    if ($classes->have_posts()):
                        echo '<ul>';
                        while ($classes->have_posts()):
                            $classes->the_post();
                            $form_url = get_the_guid();
                            echo '<li>';
                            echo '<strong>' . get_the_title() . '</strong> - ';
                            echo 'Criado por: ' . get_the_author() . ' - ';
                            echo '<a href="' . esc_url($form_url) . '" target="_blank">Acessar Formulário</a>';
                            echo '</li>';
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else:
                        echo '<p>Nenhuma classe encontrada.</p>';
                    endif;
                    ?>
                </div>

                <div id="all-entries" class="tab-content">
                    <h2>Todas as Entradas</h2>
                    <?php
                    if ($classes->have_posts()):
                        echo '<ul>';
                        while ($classes->have_posts()):
                            $classes->the_post();
                            $form_code = explode('formcode=', get_the_guid())[1];
                            $url = 'https://g.scae.academy/listar-entradas/?formcode=' . $form_code;
                            echo '<li>';
                            echo '<strong>' . get_the_title() . '</strong> - ';
                            echo '<a href="' . esc_url($url) . '" target="_blank">Acessar Entradas</a>';
                            echo '</li>';
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else:
                        echo '<p>Nenhuma classe encontrada.</p>';
                    endif;
                    ?>
                </div>

                <div id="all-students-filled" class="tab-content">
                    <h2>Todos Alunos que Já Preencheram</h2>
                    <p>Lista de todos os alunos que já preencheram.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <style>
        .class-button{
            background-color: #2980b9 !important;
            color: white !important;
            border: none;
        }

        .custom-dashboard {
            display: flex;
            gap: 20px;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
        }

        .content {
            flex: 1;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tab-content {
            display: none;
        }

        .tab-content:target {
            display: block;
        }

        .custom-dashboard .tab-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .custom-dashboard .tab-content ul li {
            background-color: #fff;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-dashboard .tab-content ul li:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .custom-dashboard .tab-content ul li strong {
            font-size: 18px;
            color: #2c3e50;
        }

        .custom-dashboard .tab-content ul li a {
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .custom-dashboard .tab-content ul li a:hover {
            background-color: #2980b9;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .custom-dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }
        }

        #classModal {
            display: none;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
    <script>
        document.getElementById("openModal").addEventListener("click", function () {
            document.getElementById("classModal").style.display = "flex";
        });

        document.querySelector(".close").addEventListener("click", function () {
            document.getElementById("classModal").style.display = "none";
        });

        window.onclick = function (event) {
            if (event.target === document.getElementById("classModal")) {
                document.getElementById("classModal").style.display = "none";
            }
        };
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('custom_dashboard', 'custom_dashboard_shortcode');

function list_form_entries_shortcode()
{
    // Verifica se o parâmetro GET 'formcode' está presente
    if (!isset($_GET['formcode'])) {
        return '<p>Nenhum formulário especificado.</p>';
    }

    $form_id = intval($_GET['formcode']); // Captura o ID do formulário

    // Verifica se o usuário está logado
    if (!is_user_logged_in()) {
        return '<p>Por favor, faça login para acessar esta página.</p>';
    }

    global $wpdb;
    $user = wp_get_current_user();
    $is_admin = in_array('administrator', (array) $user->roles);

    // Verifica se o usuário é admin ou o dono do formulário
    $form_post = $wpdb->get_row($wpdb->prepare(
        "SELECT ID, post_author FROM {$wpdb->posts} WHERE guid LIKE %s",
        '%' . $wpdb->esc_like($form_id) . '%'
    ));

    if (!$form_post) {
        return '<p>Formulário não encontrado.</p>';
    }

    if (!$is_admin && $form_post->post_author != $user->ID) {
        return '<p>Você não tem permissão para visualizar estas entradas.</p>';
    }

    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT s.id, s.serial_number, s.source_url 
         FROM {$wpdb->prefix}fluentform_submissions s 
         WHERE s.source_url LIKE %s",
        '%' . $wpdb->esc_like($form_id) . '%'
    ));

    if (empty($submissions)) {
        return '<p>Nenhuma entrada encontrada para este formulário.</p>';
    }

    // Prepara a tabela de resultados
    $output = '<div class="form-entries-table">';
    $output .= "<h2>Entradas do Formulário de Classe: $form_post->title</h2>";
    $output .= '<table>';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Nome</th>';
    $output .= '<th>Email</th>';
    $output .= '<th>Tipo do Aluno</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    foreach ($submissions as $submission) {
        $details = $wpdb->get_results($wpdb->prepare(
            "SELECT field_name, field_value 
             FROM {$wpdb->prefix}fluentform_entry_details 
             WHERE submission_id = %d 
             AND field_name IN ('names', 'email', 'input_radio_1', 'input_radio_2', 'input_radio_3', 'input_radio_4', 'input_radio_5', 'input_radio_6', 'input_radio_7', 'input_radio_8', 'input_radio_9', 'input_radio_10', 'input_radio_11', 'input_radio_12', 'input_radio_13', 'input_radio_14', 'input_radio_15', 'input_radio_16', 'input_radio_17', 'input_radio_18', 'input_radio_19', 'input_radio_20', 'input_radio_21', 'input_radio_22', 'input_radio_23', 'input_radio_24', 'input_radio_25', 'input_radio_26', 'input_radio_27', 'input_radio_28', 'input_radio_29', 'input_radio_30', 'input_radio_31', 'input_radio_32', 'input_radio_33', 'input_radio_34', 'input_radio_35', 'input_radio_36', 'input_radio_37', 'input_radio_38', 'input_radio_39', 'input_radio_40', 'input_radio_41', 'input_radio_42', 'input_radio_43', 'input_radio_44', 'input_radio_45', 'input_radio_46', 'input_radio_47', 'input_radio_48')",
            $submission->id
        ));

        $names = '';
        $email = '';

        $somaEC = 0; // Experiência Concreta
        $somaOR = 0; // Observação Reflexiva
        $somaCA = 0; // Conceituação Abstrata
        $somaEA = 0; // Experimentação Ativa

        foreach ($details as $detail) {
            if ($detail->field_name == 'names') {
                $names = $detail->field_value;
            } elseif ($detail->field_name == 'email') {
                $email = $detail->field_value;
            } elseif (strpos($detail->field_name, 'input_radio_') === 0) {
                $choicevalue = 1;
                $radio_value = intval($detail->field_value);
                switch ($choicevalue) {
                    case 2:
                        $somaEC += $radio_value;
                        break;
                    case 1:
                        $somaOR += $radio_value;
                        break;
                    case -1:
                        $somaEA += $radio_value;
                        break;
                    case -2:
                        $somaCA += $radio_value;
                        break;
                }
            }
        }

        $output .= '<tr>';
        $output .= '<td>' . esc_html($names) . '</td>';
        $output .= '<td>' . esc_html($email) . '</td>';
        $output .= '<td>' . get_learning_style($somaOR - $somaEA, $somaEC - $somaCA) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';

    return $output;
}
add_shortcode('list_form_entries', 'list_form_entries_shortcode');


add_action('wp_ajax_process_form_data', 'process_form_data_callback');
add_action('wp_ajax_nopriv_process_form_data', 'process_form_data_callback');

function process_form_data_callback() {
    if (isset($_POST['form_data']) && isset($_POST['form_id'])) {
        global $wpdb;
        $form_data = $_POST['form_data'];
        $form_id = sanitize_text_field($_POST['form_id']);

        // Extrair dados do formulário
        $username = isset($form_data['username']) ? sanitize_text_field($form_data['username']) : '';
        $useremail = isset($form_data['useremail']) ? sanitize_email($form_data['useremail']) : '';

        $pontox = 0;
        $pontoy = 0;
        $respostas_html = '';

        $placeholders_criativo = [
            '[[TIPOAPRENDIZ]]' => 'APRENDIZ CRIATIVO',
            '[[SIGNIFICADO]]' => 'Seu estilo de aprendizagem indica que você aprende melhor quando pode usar a imaginação e criar coisas novas. Você gosta de inventar, desenhar e pensar em ideias diferentes.',
            '{t1}' => 'Você é muito bom em pensar em soluções criativas.',
            '{t2}' => 'Gosta de aprender de forma divertida e diferente.',
            '{t3}' => 'Tem facilidade em conectar ideias e criar coisas novas.',
            '{d1}' => 'Pode ter dificuldade em seguir regras ou métodos muito rígidos.',
            '{d2}' => 'Às vezes, pode se distrair com muitas ideias ao mesmo tempo.',
            '{d3}' => 'Pode achar difícil estudar coisas que não permitem muita criatividade.',
            '{ty1}' => 'Use desenhos e cores para fazer resumos. Por exemplo, em vez de só escrever sobre os animais da floresta, desenhe cada animal e pinte com cores diferentes.',
            '{ty2}' => 'Crie histórias ou jogos sobre o que está estudando. Por exemplo, invente uma história sobre os planetas do sistema solar e peça para um adulto ajudar a montar um teatro de fantoches.',
            '{ty3}' => 'Trabalhe em grupo com seus amigos ou familiares para trocar ideias e criar projetos juntos. Por exemplo, faça uma maquete da cidade onde você mora com a ajuda dos seus pais.',
            '{ty4}' => 'Use materiais diferentes para aprender. Por exemplo, use massinha de modelar para criar formas geométricas ou use blocos de montar para representar o que você está estudando.',
            '{DY1}' => 'Aprendizagem criativa, uso de desenhos, histórias e projetos.',
            '{DY2}' => 'Livros de colorir, jogos educativos, vídeos animados e materiais de arte (como massinha, tinta e papel).',
            '{ATV1}' => 'Criar maquetes, desenhos ou apresentações criativas sobre o que você aprendeu.',
            '{ATV2}' => 'Fazer um diário de aprendizagem, onde você desenha ou escreve sobre o que aprendeu cada dia.',
            '{ATV3}' => 'Montar um álbum de fotos ou desenhos sobre um tema que está estudando, como os animais ou as estações do ano.',
            '{AJ1}' => 'Ajude a criança a criar projetos criativos, como maquetes, desenhos ou histórias. Por exemplo, se ela está estudando sobre o sistema solar, vocês podem juntos criar um modelo dos planetas usando bolas de isopor.',
            '{AJ2}' => 'Proponha atividades que permitam a expressão criativa, como apresentações teatrais, desenhos ou projetos em grupo.',
            '{AJ3}' => 'Convide os amigos para criar jogos ou histórias juntos. Por exemplo, façam um jogo de tabuleiro sobre o que estão estudando.'
        ];

        $placeholders_analitico = [
            '[[TIPOAPRENDIZ]]' => 'APRENDIZ ANALÍTICO',
            '[[SIGNIFICADO]]' => 'Seu estilo de aprendizagem indica que você aprende melhor quando pode observar, pensar e organizar as informações. Você gosta de entender como as coisas funcionam antes de tentar fazer algo.',
            '{t1}' => 'Você é muito bom em analisar e refletir sobre o que aprende.',
            '{t2}' => 'Gosta de ler, pesquisar e organizar informações.',
            '{t3}' => 'Tem facilidade em entender conceitos e teorias.',
            '{d1}' => 'Pode ter dificuldade em aprender coisas muito práticas ou rápidas.',
            '{d2}' => 'Às vezes, pode demorar para tomar decisões porque gosta de pensar muito.',
            '{d3}' => 'Pode achar difícil estudar coisas que não têm uma explicação clara.',
            '{ty1}' => 'Faça resumos e listas para organizar o que você aprendeu. Por exemplo, se está estudando sobre os animais, faça uma lista com o nome de cada animal e suas características.',
            '{ty2}' => 'Use gráficos e tabelas para visualizar as informações. Por exemplo, crie um gráfico mostrando quantos animais vivem na floresta, no deserto e no oceano.',
            '{ty3}' => 'Pesquise mais sobre os temas que você gosta para entender melhor. Por exemplo, se está estudando sobre o sistema solar, peça para um adulto ajudar a encontrar vídeos ou livros sobre o assunto.',
            '{ty4}' => 'Crie mapas mentais para organizar as ideias. Por exemplo, faça um mapa mental sobre as partes de uma planta, com desenhos e setas.',
            '{DY1}' => 'Uso de mapas mentais, resumos, gráficos e pesquisas.',
            '{DY2}' => 'Livros, enciclopédias, vídeos explicativos e aplicativos educativos.',
            '{ATV1}' => 'Fazer pesquisas sobre temas que você gosta.',
            '{ATV2}' => 'Criar gráficos e tabelas para organizar informações.',
            '{ATV3}' => 'Participar de debates ou discussões sobre o que está estudando.',
            '{AJ1}' => 'Ajude a criança a organizar as informações que ela aprende. Por exemplo, façam juntos um gráfico ou uma tabela sobre o que ela está estudando.',
            '{AJ2}' => 'Proponha atividades que envolvam pesquisa e organização, como trabalhos em grupo ou apresentações.',
            '{AJ3}' => 'Convide os amigos para discutir o que estão estudando e trocar ideias.'
        ];

        $placeholders_estrategista = [
            '[[TIPOAPRENDIZ]]' => 'APRENDIZ ESTRATEGISTA',
            '[[SIGNIFICADO]]' => 'Seu estilo de aprendizagem indica que você aprende melhor quando pode seguir regras e métodos organizados. Você gosta de planejar e seguir passos para resolver problemas.',
            '{t1}' => 'Você é muito bom em seguir instruções e resolver problemas de forma organizada.',
            '{t2}' => 'Gosta de aprender de forma clara e estruturada.',
            '{t3}' => 'Tem facilidade em entender conceitos abstratos e aplicá-los na prática.',
            '{d1}' => 'Pode ter dificuldade em aprender coisas que não têm uma explicação clara.',
            '{d2}' => 'Às vezes, pode se sentir desconfortável com mudanças de planos.',
            '{d3}' => 'Pode achar difícil estudar coisas que não têm uma aplicação prática imediata.',
            '{ty1}' => 'Siga um passo a passo para estudar. Por exemplo, faça uma lista do que precisa aprender e marque cada item conforme for concluindo.',
            '{ty2}' => 'Use esquemas e fluxogramas para organizar as informações. Por exemplo, crie um fluxograma mostrando as etapas do ciclo da água.',
            '{ty3}' => 'Pratique exercícios para fixar o que você aprendeu. Por exemplo, se está estudando matemática, resolva vários problemas do mesmo tipo até se sentir confiante.',
            '{ty4}' => 'Crie planos de estudo com a ajuda de um adulto. Por exemplo, defina um horário para estudar cada matéria e siga o plano à risca.',
            '{DY1}' => 'Uso de listas, esquemas, fluxogramas e exercícios práticos.',
            '{DY2}' => 'Livros didáticos, apostilas, jogos de lógica e aplicativos educativos.',
            '{ATV1}' => 'Resolver desafios e problemas práticos.',
            '{ATV2}' => 'Criar planos de estudo e seguir metas.',
            '{ATV3}' => 'Participar de competições acadêmicas ou jogos que envolvam lógica e estratégia.',
            '{AJ1}' => 'Ajude a criança a criar planos de estudo e a organizar as informações que ela aprende. Por exemplo, façam juntos um esquema sobre o que ela está estudando.',
            '{AJ2}' => 'Proponha atividades que envolvam resolução de problemas e planejamento, como projetos em grupo ou competições.',
            '{AJ3}' => 'Convide os amigos para resolver desafios juntos. Por exemplo, façam uma gincana de matemática ou ciências.'
        ];

        $placeholders_pratico = [
            '[[TIPOAPRENDIZ]]' => 'APRENDIZ PRÁTICO',
            '[[SIGNIFICADO]]' => 'Seu estilo de aprendizagem indica que você aprende melhor quando pode colocar a mão na massa e experimentar coisas novas. Você gosta de aprender fazendo e explorando.',
            '{t1}' => 'Você é muito bom em aprender coisas práticas e resolver problemas na hora.',
            '{t2}' => 'Gosta de atividades que envolvem movimento e ação.',
            '{t3}' => 'Tem facilidade em conectar o que aprende com a vida real.',
            '{d1}' => 'Pode ter dificuldade em ficar parado por muito tempo.',
            '{d2}' => 'Às vezes, pode se distrair com coisas que não parecem práticas.',
            '{d3}' => 'Pode achar difícil estudar coisas que não têm uma aplicação imediata.',
            '{ty1}' => 'Faça experiências práticas para aprender. Por exemplo, use objetos para entender matemática, como contar com blocos ou medir coisas com uma fita métrica.',
            '{ty2}' => 'Use jogos e brincadeiras para estudar. Por exemplo, crie um jogo de tabuleiro sobre o que está estudando.',
            '{ty3}' => 'Movimente-se enquanto estuda. Por exemplo, explique o que aprendeu enquanto caminha ou faça uma apresentação em voz alta.',
            '{ty4}' => 'Crie projetos práticos sobre o que está estudando. Por exemplo, se está estudando sobre plantas, plante uma semente e observe como ela cresce.',
            '{DY1}' => 'Aprendizagem prática, jogos, atividades físicas e projetos.',
            '{DY2}' => 'Jogos educativos, materiais para experiências, vídeos de experimentos e kits de ciências.',
            '{ATV1}' => 'Fazer experimentos práticos, como misturar cores ou observar reações químicas simples.',
            '{ATV2}' => 'Participar de feiras de ciências ou projetos escolares.',
            '{ATV3}' => 'Criar maquetes ou modelos sobre o que está estudando.',
            '{AJ1}' => 'Ajude a criança a fazer experiências práticas e projetos. Por exemplo, façam juntos um experimento de ciências ou uma maquete.',
            '{AJ2}' => 'Proponha atividades que envolvam movimento e prática, como jogos educativos ou projetos em grupo.',
            '{AJ3}' => 'Convide os amigos para criar projetos juntos. Por exemplo, façam uma maquete da cidade onde moram ou um jogo sobre o que estão estudando.'
        ];

        $table_name = $wpdb->prefix . 'fluentform_entry_details';

        $unique_submission_count = intval($wpdb->get_var("SELECT COUNT(DISTINCT submission_id) AS unique_submission_count FROM $table_name"));

        $unique_classes = intval($wpdb->get_var("SELECT COUNT(*) AS count FROM $wpdb->posts"));

        $professores = intval($wpdb->get_var("SELECT COUNT(*) AS count from FROM $wpdb->users"));

        $content = [
            '[[TITULO]]' => "Nos Somos o SCAE",
            '[[CONTENT1]]' => "Descubra a melhor maneira de estudar! Aqui você aprende técnicas personalizadas para potencializar seu aprendizado e alcançar seus objetivos com mais eficiência.",
            '[[CLASSES]]' => "$unique_classes",
            '[[ENTRADAS]]' => "$unique_submission_count",
            '[[PROFESSORES]]' => "$professores",
        ];

        foreach ($form_data as $key => $value) {
            if (strpos($key, 'input_radio_') === 0) {
                $number = intval(str_replace('input_radio_', '', $key));
                
                if($number % 4 == 1){
                    $pontox += intval($value);
                }else if($number % 4 == 2){
                    $pontox -= intval($value);
                }else if($number % 4 == 3){
                    $pontoy += intval($value);
                }else if($number % 4 == 0){
                    $pontoy -= intval($value);
                }
            }
        }

        $tipo = get_learning_style($pontox, $pontoy);
        
        //wp_send_json_error("Data: $tipo; $pontox; $pontoy");

        $email_template_path = plugin_dir_path(__FILE__) . 'email.html';

        if (!file_exists($email_template_path)) {
            wp_send_json_error('Arquivo de template de email não encontrado.');
        }

        $email_content = file_get_contents($email_template_path);

        $basetemplate = [];

        if($tipo == 1){ //'Divergente'
            $basetemplate = $placeholders_analitico;
        }else if($tipo == 4){ //'Acomodador'
            $basetemplate = $placeholders_estrategista;
        }else if($tipo == 3){ // 'Convergente'
            $basetemplate = $placeholders_pratico;
        }else if($tipo == 2){
            $basetemplate = $placeholders_criativo;
        }else{
            wp_send_json_error("Erro ao enviar email.");
        }

        $basetemplate = array_merge($basetemplate, $content);
        
        $email_content = strtr($email_content, $basetemplate);

        //file_put_contents("teste.html", $email_content);

        $subject = 'SCAE (MCES) - Mapa Cognitivo e Experiencial';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $email = wp_mail($useremail, $subject, $email_content, $headers);
                
        if ($email) {
            wp_send_json_success('Email enviado com sucesso!');
        } else {
            wp_send_json_error('Erro ao enviar o email.');
        }
    } else {
        wp_send_json_error('Nenhum dado ou ID de formulário recebido.');
    }
}

function get_learning_style($x, $y)
{
    if ($x < 0 && $y > 0) {
        return 1;  // (EC + OR) "Divergente"
    } elseif ($x < 0 && $y < 0) {
        return 2;  // (CA + OR) "Assimilador"
    } elseif ($x > 0 && $y < 0) {
        return 3;  // (CA + EA) "Convergente"
    } elseif ($x > 0 && $y > 0) {
        return 4;   // (EC + EA) "Acomodador"
    } else {
        return 5;   // Caso X ou Y seja exatamente 0
    }
}