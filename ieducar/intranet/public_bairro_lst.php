<?php

/**
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu��do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl��cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package   Ied_Public
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsListagem.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/public/geral.inc.php';

require_once 'App/Model/ZonaLocalizacao.php';
require_once 'CoreExt/View/Helper/UrlHelper.php';

/**
 * clsIndexBase class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Public
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' Bairro');
    $this->processoAp = 756;
  }
}

/**
 * indice class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Public
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsListagem
{
  var $__pessoa_logada;
  var $__titulo;
  var $__limite;
  var $__offset;

  var $idmun;
  var $geom;
  var $idbai;
  var $nome;
  var $idpes_rev;
  var $data_rev;
  var $origem_gravacao;
  var $idpes_cad;
  var $data_cad;
  var $operacao;
  var $idsis_rev;
  var $idsis_cad;

  var $idpais;
  var $sigla_uf;

  function Gerar()
  {
    @session_start();
    $this->__pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    $this->__titulo = 'Bairro - Listagem';

    // Passa todos os valores obtidos no GET para atributos do objeto
    foreach ($_GET as $var => $val) {
      $this->$var = ($val === '') ? NULL : $val;
    }

    $this->addBanner('imagens/nvp_top_intranet.jpg',
      'imagens/nvp_vert_intranet.jpg', 'Intranet');

    $this->addCabecalhos(array(
      'Nome',
      'Zona Localiza��o',
      'Munic�pio',
      'Estado',
      'Pais'
    ));

    // Filtros de Foreign Keys
    $opcoes = array('' => 'Selecione');

    if (class_exists('clsPais')) {
      $objTemp = new clsPais();
      $lista = $objTemp->lista(FALSE, FALSE, FALSE, FALSE, FALSE, 'nome ASC');

      if (is_array($lista) && count($lista)) {
        foreach ($lista as $registro) {
          $opcoes[$registro['idpais']] = $registro['nome'];
        }
      }
    }
    else {
      echo "<!--\nErro\nClasse clsPais nao encontrada\n-->";
      $opcoes = array('' => 'Erro na gera��o');
    }

    $this->campoLista('idpais', 'Pais', $opcoes, $this->idpais, '', FALSE, '',
      '', FALSE, FALSE);

    $opcoes = array('' => 'Selecione');

    if (class_exists('clsUf')) {
      if ($this->idpais) {
        $objTemp = new clsUf();
        $lista = $objTemp->lista(FALSE, FALSE, $this->idpais, FALSE, FALSE,
          'nome ASC');

        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['sigla_uf']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo "<!--\nErro\nClasse clsUf nao encontrada\n-->";
      $opcoes = array('' => 'Erro na gera��o');
    }

    $this->campoLista('sigla_uf', 'Estado', $opcoes, $this->sigla_uf, '', FALSE,
      '', '', FALSE, FALSE);

    $opcoes = array('' => 'Selecione');

    if (class_exists('clsMunicipio')) {
      if ($this->sigla_uf) {
        $objTemp = new clsMunicipio();
        $lista = $objTemp->lista(FALSE, $this->sigla_uf, FALSE, FALSE, FALSE,
          FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, 'nome ASC');

        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['idmun']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo "<!--\nErro\nClasse clsMunicipio nao encontrada\n-->";
      $opcoes = array('' => 'Erro na gera��o');
    }

    $this->campoLista('idmun', 'Munic�pio', $opcoes, $this->idmun, '', FALSE,
      '', '', FALSE, FALSE);

    // Outros filtros
    $this->campoTexto('nome', 'Nome', $this->nome, 30, 255, FALSE);

    // Paginador
    $this->__limite = 20;
    $this->__offset = ($_GET['pagina_' . $this->nome]) ?
      ($_GET['pagina_' . $this->nome] * $this->__limite - $this->__limite) : 0;

    $obj_bairro = new clsPublicBairro();
    $obj_bairro->setOrderby('nome ASC');
    $obj_bairro->setLimite($this->__limite, $this->__offset);

    $lista = $obj_bairro->lista(
      $this->idmun,
      NULL,
      $this->nome,
      NULL,
      NULL,
      NULL,
      NULL,
      NULL,
      NULL,
      NULL,
      NULL,
      NULL,
      NULL,
      $this->idpais,
      $this->sigla_uf
    );

    $total = $obj_bairro->_total;

    // Zona Localiza��o.
    $zona = App_Model_ZonaLocalizacao::getInstance();

    // UrlHelper.
    $url = CoreExt_View_Helper_UrlHelper::getInstance();
    $options = array('query' => array('idbai' => NULL));

    // Monta a lista.
    if (is_array($lista) && count($lista)) {
      foreach ($lista as $registro) {
        $zl = $zona->getValue($registro['zona_localizacao']);
        $options['query']['idbai'] = $registro['idbai'];

        $this->addLinhas(array(
          $url->l($registro['nome'], 'public_bairro_det.php', $options),
          $url->l($zl, 'public_bairro_det.php', $options),
          $url->l($registro['nm_municipio'], 'public_bairro_det.php', $options),
          $url->l($registro['nm_estado'], 'public_bairro_det.php', $options),
          $url->l($registro['nm_pais'], 'public_bairro_det.php', $options)
        ));
      }
    }

    $this->addPaginador2('public_bairro_lst.php', $total, $_GET, $this->nome, $this->__limite);

    $this->acao      = 'go("public_bairro_cad.php")';
    $this->nome_acao = 'Novo';

    $this->largura = '100%';
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do � p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();
?>
<script type="text/javascript">
document.getElementById('idpais').onchange = function()
{
  var campoPais = document.getElementById('idpais').value;

  var campoUf= document.getElementById('sigla_uf');
  campoUf.length = 1;
  campoUf.disabled        = true;
  campoUf.options[0].text = 'Carregando estado...';

  var xml_uf = new ajax(getUf);
  xml_uf.envia('public_uf_xml.php?pais=' + campoPais);
}

function getUf(xml_uf)
{
  var campoUf   = document.getElementById('sigla_uf');
  var DOM_array = xml_uf.getElementsByTagName('estado');

  if (DOM_array.length) {
    campoUf.length          = 1;
    campoUf.options[0].text = 'Selecione um estado';
    campoUf.disabled        = false;

    for (var i = 0; i < DOM_array.length; i++) {
      campoUf.options[campoUf.options.length] = new Option(
        DOM_array[i].firstChild.data, DOM_array[i].getAttribute('sigla_uf'),
        false, false
      );
    }
  }
  else {
    campoUf.options[0].text = 'O pais n�o possui nenhum estado';
  }
}

document.getElementById('sigla_uf').onchange = function()
{
  var campoUf = document.getElementById('sigla_uf').value;

  var campoMunicipio      = document.getElementById('idmun');
  campoMunicipio.length   = 1;
  campoMunicipio.disabled = true;

  campoMunicipio.options[0].text = 'Carregando munic�pio...';

  var xml_municipio = new ajax(getMunicipio);
  xml_municipio.envia('public_municipio_xml.php?uf=' + campoUf);
}

function getMunicipio(xml_municipio)
{
  var campoMunicipio = document.getElementById('idmun');
  var DOM_array      = xml_municipio.getElementsByTagName( "municipio" );

  if (DOM_array.length) {
    campoMunicipio.length          = 1;
    campoMunicipio.options[0].text = 'Selecione um munic�pio';
    campoMunicipio.disabled        = false;

    for (var i = 0; i < DOM_array.length; i++) {
      campoMunicipio.options[campoMunicipio.options.length] = new Option(
        DOM_array[i].firstChild.data, DOM_array[i].getAttribute('idmun'),
        false, false
      );
    }
  }
  else {
    campoMunicipio.options[0].text = 'O estado n�o possui nenhum munic�pio';
  }
}
</script>