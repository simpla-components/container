# Simpla Framework

* **[Container de Serviços](#container)** 
	* [Serviço](#serviço)
	* [Service Container](#service-conainer)
	* [Estruturando um Projeto para Service Container](#estrutura)
	* [Criando um Serviço Orientado a Interface](#interface)
	* [Adicionando o Serviço no Container](#add-container)
		* [Make](#make)
		* [Array](#array)
		* [Singleton](#singleton)
	* [Recuperando um Serviço](#recuperar)
	* [Adicionando Dados aos Container](#add-dados)
	* [Chamada de Serviços (injeção de dependência)](#injeacao)
	* [Adicionando Interface](#add-interface)
	* [Adicionando Closures](#add-closure)
	* [Obtendo a função de criação de serviço](#func-servico)
	* [Modificando Serviços após Definição](#mod-services)
* **[Service Provider](#service-provider)**
	* [O método Register](#register)
	* [O método Boot](#boot)
	* [Bootstrap](#bootstrap)
	* [Opção Defer](#defer)
* **[Facades](#facades)**
	* [Como as facades funcionam](#como-facades)

------


## <a name="container">Container de Serviços</a>

O conceito de Container de Serviços está intrissecamente relacionado com objetos e, logicamente à Programação Orientada a Objetos (POO). De fato, todo serviço pode ser um objeto mas, nem todo objeto será um serviço. 
Uma aplicação modernas temos diversos objetos ou conjunto de objetos, cada um com uma função específica. Assim, temos objetos que nos ajudam a enviar um e-mail, acessar e registrar informações em bancos de dedos, etc. Mas, o que é um serviço?

## <a name="serviço">Serviço</a>

A [documentação do Symfony](http://andreiabohner.org/documentacao-do-symfony/3.1/service_container.html#o-que-e-um-servico) define um serviço de uma forma bastante clara:

<div class="note">
Um Serviço é qualquer objeto PHP que realiza algum tipo de tarefa “global”.  É um nome genérico proposital, usado em ciência da computação, para descrever um objeto que é criado para uma finalidade específica (por exemplo, entregar e-mails). Cada serviço é usado em qualquer parte da sua aplicação sempre que precisar da funcionalidade específica que ele fornece. Você não precisa fazer nada de especial para construir um serviço: basta escrever uma classe PHP com algum código que realiza uma tarefa específica. Parabéns, você acabou de criar um serviço!
</div>

Este conceito está presente na [arquitetura orientada a serviços](https://en.wikipedia.org/wiki/Service-oriented_architecture) onde cada "serviço" pode ser acessado e utilizado facilmente, sem interferir no funcionamento de outro serviço ou da aplicação.

## <a name="service-conainer">Service Container</a>

Um Container de Serviço é um objeto PHP responsável pelo gerenciamento e o instanciamento de serviços (objetos ou outros elementos). 

Diversos Frameworks, como Laravel, Zend, Symfony, Silex e Slim também implementam Services Containers.

O Simpla utiliza como base o Pimple, Service Container desenvolvido e mantido pela Symfony e utilizado pelo Silex, sendo que é utilizada a versão que implementa a [PSR11](https://www.php-fig.org/psr/psr-11/) .

### <a name="estrutura">Estruturando um Projeto para Service Container</a>

Neste exemplo iremos criar uma estrutura padrão para utilização de um service container. Essa é a mesma estrutura utilizada pelo Simpla Framework, porém vamos nos ater ao exemplo abaixo.

* A seguir definimos em "Core" o local onde serão armazenados todos os serviços criados.
* Em "Http/Controllers" teremos uma classe que poderá utilizar aquele serviço.
* Em "Providers" teremos os Provedores de Serviço.
* Em "bootstrap" teremos um inicializador automático de serviços.

        /__app
        |    |__Core
        |    |   |__ [Services.php]
        |    |__Http
        |    |   |__Controllers
        |    |       |__ [UsingServices.php]
        |    |__Providers
        |
        |__bootstrap
        |    |__providers.php
        |
        |__index.php

### <a name="interface">Criando um Serviço Orientado a Interface</a>

Um serviço nada mais é que um objeto, e objeto é definido em uma classe. Porém, antes de construírmos uma classe podemos definir uma inteface para o objeto.

Neste sentido vamos definir serviço de calculadora simples conforme a seguir:

```php

    namespace App\Classes;

    interface CalculatorInterface
    {
        public function sum($a, $b);
        public function subtract($a, $b);    
        public function multiply($a, $b);    
        public function divide($a, $b);    
    }

``` 

Interfaces são como contratos de comportamentos obrigatórios disponíveis ao ambiente externo, onde são declarados métodos e constantes públicas, além disso as interfaces desempenham papel importante no [desacoplamento de código](https://pt.wikipedia.org/wiki/Acoplamento_(inform%C3%A1tica))  em uma aplicação orientada a objeto.

Interfaces definem quais métodos devem ser implementados de forma obrigatória pela classe que à implementar. 

A utilidade desse prática é um menor acoplamento do código. Quando sua aplicação necessitar de efetuar um cálculo de soma ela não ficará refém da "CalculadoraDoFulano", podemos substituir pela "CalculadorMelhorDoCiclano" sem a necessidade de trocar em toda a aplicação a implementação da soma por que en "CalculadoraDoFulano", "sum" foi implementado como `sum($a, $b = null)` e "CalculadoraMelhorDoCiclano" implementou `add($a, $b)`.


Assim, vamos implementar a Interface:

```php

    namespace App\Classes;

    class Calculator implements CalculatorInterface
    {
        public function sum($a, $b)
        {
            return $a + $b;
        }

        public function subtract($a, $b)
        {
            return $a - $b;
        }

        public function multiply($a, $b)
        {
            return $a * $b;
        }

        public function divide($a, $b)
        {
            return $a / $b;
        }
    }
```

### <a name="add-container">Adicionando o Serviço no Container</a>

Uma vez definido o serviço podemos usá-lo simplimente com `$calc = new Calculator()`. Porém, às vezes isso pode não ser funcional do ponto de vista do gerenciamento da aplicação. Quantos objetos instanciados existem na aplicação? Devemos definir os serviços como Singleton? 

Para simplificar o gerencimento dos objetos podemos fazer o uso do Container de Serviço utilizando as opções a seguir:

#### <a name="make">Make</a>

Vamos carregar em nosso arquivo index.php o Container do Simpla e "injetar" o container do Pimple, conforme a seguir:

```php

    use Simpla\Container\Container;
    use Xtreamwayz\Pimple\Container as Pimple;


    /* @var $app Simpla\Container\Container */
    $app = Container::instance(new Pimple);
```

O **Container** é um singleton que garante que haja uma única instancia dele em toda a aplicação, assim, temos garantido que só temos um controller em toda a aplicação.

Opcionalmente podemos definir `$app` como sendo um objeto global, para ser acessado em toda a aplicação:

```php

    global $app;
```

Podemos adicionar um serviço com o método `make`:

```php

    $app->make("calc", function(){
         return new \App\Core\Calculator();
    });

```
 
Se por algum motivo o objeto já foi definido podemos simplemente incluí-lo, ao invés de uma closure:

```php

    $calc = new \App\Classes\Calculator();
    $app->make("calc", $calc);

    // ou diretamente

    $app->make("calc", new \App\Classes\Calculator());

```


Podemos adicionar um serviço também pela informação da assinatura da classe:

```php

    $app->make("calc", \App\Classes\Calculator::class);


```

Se não quisermos definir um nome para o serviço, podemos simplesmente informar a assinatura da classe como único parâmetro.

```php

    $app->make(\App\Classes\Calculator::class);


```


A desvantagem nesta opção é a necessidade de informar `\App\Classes\Calculator::class` como nome do serviço, o que pode ser um pouco cansativo.
 

#### <a name="array">Array</a>

Por implementar a interface `ArrayAccess` o Simpla Container permite que possamos adicionar um serviço da mesmo forma que adicionamos valores em um array:

```php

    $app['calc1'] = new \App\Classes\Calculator();

    // Ou ainda

    $app['calc2'] = function(){
        return new \App\Classes\Calculator();    
    };

```


#### <a name="singleton">Singleton</a>


Utilizando o método `singleton` garantimos que apenas uma única instância (objeto) de uma classe exista.

```php

    $app->singleton("calc", function(){
        return new Calculator();
    });

    $calc1 = $app["calc"];
    $calc2 = $app["calc"];
    $calc3 = $app["calc"];

    // $calc1 = $calc2 = $calc3

```

Uma característica do Pimple, que se aplica nesta situação é a adição de serviço como array. 

```php


    $app['calc'] = new \App\Classes\Calculator();

    $calc1 = $app["calc"];
    $calc2 = $app["calc"];
    $calc3 = $app["calc"];

    // $calc1 = $calc2 = $calc3

```


#### <a name="recuperar">Recuperando um Serviço</a>

Os serviços podem ser recuperados pelo método `get`.

```php

    $calc = $app->get("calc");
    $calc=>sum(34,54);
    
    //Usando diretamente

    $app->get("calc")->subtract(32,12);

```

Tembém podemos recuperar um serviço com se este fosse um array:

```php

    $calc = $app['calc'];
    $calc=>sum(34,54);
    
```


### <a name="add-dados">Adicionando Dados aos Container</a>

O Container também pode conter qualquer outro tipo de informação que não seja um objeto. Isso é extremamente útil para incluir informações que possam ser utilizadas globalmente na aplicação.

```php

    $app['tz.br.spo'] = "America/Sao_Paulo";
    $app['tz'] = new DateTimeZone($app['tz.br.spo']);

    $app->make("today", new DateTime("now", $app['tz']));

```

#### <a name="injeacao">Chamada de Serviços (injeção de dependência)</a>

O método `call` permite chamar um método de um serviço pré-definido com a sintaxe "service@method", passando um array com todos os parâmetros.

```php

    $show = $app->call("today@format", ['d/m/Y H:i:s']); // 02/10/2017 21:11:25
    
```
Desta forma estamos injetando uma dependência em um método de um serviço.
 
### <a name="add-interface">Adicionando Interface</a>

Podemos definir um serviço com o nome de sua interface.

```php

    $app->make(App\Classes\CalculatorInterface::class, \App\Classes\Calculator::class);

    var_dump($app->get());

    $calcs = $app[App\Classes\CalculatorInterface::class];

```

Para simplificar podemos estabelecer uma **tag** para aquela interface, como se fosse um nome mesmo. 

```php

    $app->tagged("ICalc", App\Classes\CalculatorInterface::class);

    $app->make("ICalc", \App\Classes\Calculator::class);

    // também podemos definir como array ou singleton

    $app["ICalc"] = new \App\Classes\Calculator;
    
    $app->singleton("ICalc", \App\Classes\Calculator::class);


```

#### <a name="add-closure">Adicionando Closures</a>

Podemos adicionar closures no container como serviços.

```php
 
    $app["sum"] = $app->closure(function ($a, $b) {
        return $a + $b;
    });

    $rand = $app["sum"];
    
    var_dump($rand(4,12)); // 16


```


#### <a name="func-servico">Obtendo a função de criação de serviço</a>

Quando você acessa um objeto, o Pimple chama automaticamente a função anônima que você definiu, o que cria o objeto de serviço para você. Se você quiser obter acesso bruto a esta função, você pode usar o método `raw()`:


```php
 
    $calc = $app->raw('calc');

    var_dump($calc()->sum(5,65)); // 70

```
Desta forma `$calc` obteve uma função anônima contendo a implementação do serviço **calc**. Ao chamarmos `$calc` como uma função, o serviço é criado (é criado o objeto).


#### <a name="mod-services">Modificando Serviços após Definição</a>


Em alguns casos você pode querer modificar uma definição de serviço depois de ter sido definido. Você pode usar o método `extend()`  para definir código adicional para ser executado em seu serviço logo após ele é criado:

```php

    class Car
    {
        private $placa;
        private $ano; 

        function getPlaca()
        {
            return $this->placa;
        }

        function getAno()
        {
            return $this->ano;
        } 

        function setPlaca($placa)
        {
            $this->placa = $placa;
        }

        function setAno($ano)
        {
            $this->ano = $ano;
        }  
    }

    $app['car'] = function(){
        return new Car();
    };

    $app['car'] = $app->extend('car', function($car){
        $car->setPlaca("HNT-2299");
        $car->setAno(2010);

        return $car;
    });


    var_dump($app['car']);

    /*
        object(Car)#37 (2) {
            ["placa":"Car":private]=>
                string(8) "HNT-2299"
            ["ano":"Car":private]=>
                int(2010)
        }
    */
```

O primeiro argumento é o nome do serviço para estender, a segunda uma função que obtém acesso à instância do objeto e do recipiente.


## <a name="service-provider">Service Provider</a>

Em um tradução direta um Service provider é um *Provedor de Serviço*, ou seja:
**Provedor**: O que provê algo. 
**Serviço**: Ato ou efeito de servir.
Provedor de serviço: O que provê um serviço.

O Service Provider complementa o uso do Container pois, serve como um inicializador de um serviço, provendo a este todos os módulos necessários para que seja iniciado corretamente. Em um contexto de Orientação a Objetos é como se eu configurasse todos os parametros que um construtor de uma classe necessita para ser iniciada.

```php
namespace App\Providers;
 
use Simpla\Contracts\ServiceProviderInterface;
use Simpla\Contracts\ContainerInterface;

class TodayServiceProvider implements ServiceProviderInterface
{ 
		/**
		 * Register the services 
		 *
		 * @return void
		 */	
    	public function register(ContainerInterface $serviceContainer)
    	{ 
    		$tz = new \DateTimeZone("America/Sao_Paulo");
    		
        	$serviceContainer->make("today", function(){
	        	   return  new DateTime("now");
        	});         
    	} 
}
 
```

### <a name="register">O método Register</a>

 Perceba que nosso Service Provider foi criado com o nome de **TodayServiceProvider** e conta com o método de registro (register), onde definimos nosso serviço.
 
 Para definir o serviço temos como parâmetro o nosso Container definido com a interface `ContainerInterface`, assim podemos utilizar o container e realizar o registro do serviço.

Para entender melhor, vamos adicionar em nossa estrutura um provedor:

        /__app
        |    |__Core
        |    |   |__ Calculator.php
        |    |__Http
        |    |   |__Controllers
        |    |       |__ Home.php
        |    |__Providers
        |           |__ CalculatorServiceProvider.php
        |__bootstrap
        |    |__providers.php
        |    |__bootstrap.php
        |
        |__index.php


A forma de trabalho do service provider no Simpla é muito similar a do [Laravel](https://laravel.com/docs/5.6/providers). 

Um outro exemplo de provedor de serviço pode ser visto a seguir:

```php
// CalculatorServiceProvider.php
namespace App\Providers;
 
use Simpla\Contracts\ServiceProviderInterface;
use Simpla\Contracts\ContainerInterface;

class CalculatorServiceProvider implements ServiceProviderInterface
{ 
		/**
		 * Register the services 
		 *
		 * @return void
		 */	
    	public function register(ContainerInterface $serviceContainer)
    	{ 
        	$serviceContainer->make("calc", function(){
	        	   return  new \App\Classes\Calculator();
        	});         
    	} 
}
 
```

Este provider foi criado mas, só pode ser utilizado quando o adicionarmos ao nosso Container. Para isso utilizamos o método `register` do container.

```php
	$app->register(new \App\Providers\CalculatorServiceProvider());
	
	$calc = $app['calc'];
	
	$calc->sum(13.5,432.3) // 445.8
``` 

Isso pode ser dispendioso se pensarmos que teremos de adicionar todos os Services Providers no container, mas veremos  o quão prático torna-se essa metodologia quando utilizamos um [Bootstrap](#bootstrap).

### <a name="boot">O método Boot</a>

Podemos ainda, adicionar um método `boot` no Service Provider. Este método é chamado depois que todos os serviços foram registrados, permitindo acessar todos os serviços que foram inicializados antes deste.

O método `boot` deve ser utilizado para inicializar um serviço. Nesta opção podemos interagir com o serviço, inicializando dependências deste e quaisquer eventos que devam ser executados antes do serviço ser criado/chamado.

```php
// CalculatorServiceProvider.php
namespace App\Providers;
 
use Simpla\Contracts\ServiceProviderInterface;
use Simpla\Contracts\ContainerInterface;

class TestServiceProvider implements ServiceProviderInterface
{ 
		/**
		 * Bootstrap the services 
		 *
		 * @return void
		 */	
    	public function boot(ContainerInterface $app)
    	{
    		echo "Isso é uma mensagem de inicialização";
    		
    		//Imprimindo serviços disponíveis
    		print_r($app->get());
    	}
		/**
		 * Register the services 
		 *
		 * @return void
		 */	
    	public function register(ContainerInterface $serviceContainer)
    	{ 
        	// code    
    	} 
}

```
 
## <a name="bootstrap" class="item-1">Bootstrap</a>

Para que possamos criar nossos serviços de uma forma mais automatizada podemos fazer uso de um inicializador. Assim podemos utilizar uma arquivo contendo todos os providers que desejamos chamar.



        /__app
        |    |__Core
        |    |   |__ [Services.php]
        |    |__Http
        |    |   |__Controllers
        |    |       |__ [UsingServices.php]
        |    |__Providers
        |
        |__bootstrap
        |    |__providers.php
        |    |__bootstrap.php
        |
        |__index.php

Adotando a estrutura acima, podemos definir em um array no arquivo `providers.php` todos os serviços que desejamos chamar:

```php
<?php

    $providers = [
    			App\Providers\CalculatorServiceProvider::class,
                       App\Providers\CarServiceProvider::class,
                       App\Providers\HelloServiceProvider::class
                   ];
                   
	 $aliases = [
	     'calc' => App\Facades\CalculatorFacade::class,
	     'hello' => App\Facades\HelloFacade::class,
	     'car' => \App\Facades\CarServiceProvider::class
	 ];

```

Também devemos adicionar neste arquivo os 'aliases' para cada provider, o que permite a utilização das **[facades](#facades) **, se existirem. Se não existir facade devemos adicionar em 'aliases' a assinatura da classe ou provider, como no exemplo acima em `\App\Facades\CarServiceProvider::class`.

Saiba mais sobre [facades aqui](#facades).

Podemos ainda definir no arquivo `bootstrap.php` o nosso container:

```php
	require __DIR__.'/../bootstrap/providers.php';

	use Simpla\Container\Container;
	use Xtreamwayz\Pimple\Container as Pimple;
	 
	global $app;

	/* @var $app Simpla\Container\Container */
	$app = Container::instance(new Pimple);

	$app->createAlias($aliases);
	$app->registerProviders($providers); 

```
 
 Podemos carregar nosso arquivo `bootstrap.php` em uma **index.php** e utilizar todos os serviços disponíveis. 

### <a name="defer">Opção Defer</a>

A opção defer (deferred/adiado) faz com que o Service Provider só seja registrado no momento em que se precisa dele. Ou seja, o container somente registra o serviço quando ele é chamado.

Esta opção é extremamente útil pois, evita que serviços desnecessários sejam carregados automaticamente em uma aplicação.

Você pode obter a lista dos serviços 'deferidos' com o comando `getDeferredServices()` do container.

Para definir que um serviço será carregado de forma adiada devemos definir a propriedade `$defer` como `true` e retornando em um metodo `provider()` um array com o nome dos serviços.

```php
class CalculatorServiceProvider implements ServiceProviderInterface
{
	    protected $defer = true;
	  
	    public function register(ContainerInterface $serviceContainer)
	    {
			$calc = new \App\Classes\Calculator();

			$serviceContainer->make("calc", $calc);
			$serviceContainer->make("calcSum",  
				$serviceContainer->closure(function ($a, $b) {
				    return $a + $b;
				})
			);
	    }

	    public static function providers()
	    {
			return ['calc','callSum'];
	    }
}
```
 
## <a name="facades" class="item-1">Facades</a>

As Facades do **Simpla** são bem parecidas com as Facades do framework Laravel.

As Facades neste contexto permitem que tenhamos acesso aos serviços de forma simplificada, como se estes fossem classes com métodos estáticos. Isso não quer dizer que os serviços sejam estáticos de verdade, a facade cria uma nova forma de acessar um serviço, uma "fachada".

### <a name="como-facades">Como as facades funcionam</a>

Para que as facades funcionem precisamos defini-las e adiciona-las em nosso arquivo de bootstrap.

```php
<?php

    $providers = [
   			App\Providers\CalculatorServiceProvider::class,
                       App\Providers\CarServiceProvider::class,
                       App\Providers\HelloServiceProvider::class
                   ];
                   
	 $aliases = [
	     'calc' => App\Facades\CalculatorFacade::class,
	     'hello' => App\Facades\HelloFacade::class,
	     'car' => \App\Facades\CarServiceProvider::class
	 ];

```

a variável `$aliases` é responsável por armazenar o apelido para nossas facades. 

Para cada serviço devemos criar um arquivo de facade que, em nossa estrutura foi adicionado em `App\Facades`, conforme definido no exemplo a seguir:

```php
// CalculatorFacade.php

namespace App\Facades;                  
 
class CalculatorFacade extends Simpla\Container\Facade
{
    public static function createService()
    {
        return 'calc';
    }
}
```

Desta forma podemos chamar o serviço `Calculator` como:

```php
	use App\Facades\CalculatorFacade;

	calc::sum(43,21); // 63
```

O nome definido no método `createService()` e no arquivo de *bootstrap* devem coincidir, caso contrário um `NotFoundException` será lançada.
 
  