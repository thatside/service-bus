## Table of contents
* [Что такое саги](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%A7%D1%82%D0%BE-%D1%82%D0%B0%D0%BA%D0%BE%D0%B5-%D1%81%D0%B0%D0%B3%D0%B8)
* [Область применения](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%9E%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C-%D0%BF%D1%80%D0%B8%D0%BC%D0%B5%D0%BD%D0%B5%D0%BD%D0%B8%D1%8F)
* [Особенности реализации](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%9E%D1%81%D0%BE%D0%B1%D0%B5%D0%BD%D0%BD%D0%BE%D1%81%D1%82%D0%B8-%D1%80%D0%B5%D0%B0%D0%BB%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8)
* [Минусы при использовании](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%9C%D0%B8%D0%BD%D1%83%D1%81%D1%8B-%D0%BF%D1%80%D0%B8-%D0%B8%D1%81%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B8)
* [Конфигурация](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%9A%D0%BE%D0%BD%D1%84%D0%B8%D0%B3%D1%83%D1%80%D0%B0%D1%86%D0%B8%D1%8F)
* [Жизненный цикл](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%96%D0%B8%D0%B7%D0%BD%D0%B5%D0%BD%D0%BD%D1%8B%D0%B9-%D1%86%D0%B8%D0%BA%D0%BB)
* [Создание](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%A1%D0%BE%D0%B7%D0%B4%D0%B0%D0%BD%D0%B8%D0%B5)
* [Примеры кода](https://github.com/mmasiukevich/service-bus/blob/master/doc/ru_sagas.md#%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B-%D0%BA%D0%BE%D0%B4%D0%B0)

#### What is Saga?
Saga may be interpreted as any documented business process which consists of steps. Speaking technically, Saga is an Event Listener which listens to some [event](https://github.com/mmasiukevich/service-bus/blob/master/doc/en_messages.md#event) and performs an action based on that event. A good example is a flowchart with a decision symbol.

There are synchronous, asynchronous and mixed sagas (where some steps may be performed synchronously and some asynchronously). From personal experience only asynchronous sagas are worth implementing.

A little bit more on [Saga](https://microservices.io/patterns/data/saga.html).

#### Field of use
* Description of complicated business processes. A good example is electronic document management. Transfer of a document may take some time - up to weeks depending on many factors. The process itself consists of a dozen of steps including electronic signatures of 3 parties (3 party is the operator)
* Distributed transactions (emulated Atomicity): either each step would be finished successfully or a step-specific compensating action will be performed.

#### Implementation features
All the sagas in the framework are asynchronous and have their own state which is serialized and stored in database. Any variables expect closures may be used as saga state.
Any non-closed saga may be triggered starting from any of saga's steps if a corresponding event is received.

#### Caveats
The more sagas (and steps within them) you have the more documentation you need for them. Single saga may trigger other sagas heavily increasing complexity of business processes.
[Message based architecture](https://www.enterpriseintegrationpatterns.com/patterns/messaging/Messaging.html) is very difficult to understand at the beginning (especially after "common" PHP programming) and requires a lot of responsibility.

#### Configuration
Sagas are configures through annotations [@SagaHeader](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Annotations/SagaHeader.php) and [@SagaEventListener](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Annotations/SagaEventListener.php).
Parameters:
 - ```idClass```: Saga identifier class namespace;
 - ```expireDateModifier```: Saga expiry interval;
 - ```containingIdProperty```: Field (in the event) where saga identifier should be found.
 Each event should be bound to specific saga instance (since one message may be received by different sagas) - that's why there should be an identifier in each of saga events. This parameter may be overridden per event listener class.
 
 Each event listener should be named like ```on{EventName}```, where *on* is a generic prefix and *{EventName}* is short class name.
 
 #### Lifecycle
 Saga execution starts on call of method [start()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Saga.php#L133), which will be called automatically (see example below). There are following methods (protected) available inside Saga instance:
- [fire()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Saga.php#L191): Dispatches a command;
- [raise()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Saga.php#L174): Dispatches an event;
- [makeCompleted()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Saga.php#L209): Closes saga marking it as successfully finished.
- [makeFailed()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Saga.php#L228): Closes saga marking it as failed.

On saga status change next events will be raised:
- [SagaCreated()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Contract/SagaCreated.php): Saga was created (started);
- [SagaStatusChanged()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Contract/SagaStatusChanged.php): Saga status was changed;
- [SagaClosed()](https://github.com/mmasiukevich/service-bus/blob/master/src/Sagas/Contract/SagaClosed.php): Saga was closed;

#### Creation
There is a specific provider created for more convenient operations with sagas - [SagaProvider](https://github.com/mmasiukevich/service-bus/blob/master/src/SagaProvider.php). Each of its methods returns [Promise](https://github.com/amphp/amp/blob/master/lib/Promise.php) object.
- [start()](https://github.com/mmasiukevich/service-bus/blob/master/src/SagaProvider.php#L78): Creates and starts new saga firing a command;
- [obtain()](https://github.com/mmasiukevich/service-bus/blob/master/src/SagaProvider.php#L126): Retrieves a saga instance from database;
- [save()](https://github.com/mmasiukevich/service-bus/blob/master/src/SagaProvider.php#L161): Saves all changes in saga state and sends all saga events to transport.

#### Example

```php
<?php

declare(strict_types = 1);

namespace Desperado\ServiceBus;

use Desperado\ServiceBus\Common\Contract\Messages\Command;
use Desperado\ServiceBus\Sagas\Annotations\SagaEventListener;
use Desperado\ServiceBus\Sagas\Annotations\SagaHeader;
use Desperado\ServiceBus\Sagas\Saga;
use Desperado\ServiceBus\Sagas\SagaId;

/**
 * @SagaHeader(
 *     idClass="Desperado\ServiceBus\ExampleSagaId",
 *     expireDateModifier="+1 year",
 *     containingIdProperty="operationId"
 * )
 */
final class ExampleSaga extends Saga
{
    /**
     * @inheritDoc
     */
    public function start(Command $command): void
    {
        $this->fire(
            new SomeCommand(/** params */)
        );
    }

    /**
     * @SagaEventListener()
     *
     * @param SomeEvent $event
     *
     * @return void
     */
    private function onSomeEvent(SomeEvent $event): void
    {
        $this->fire(
            new NextCommand(/**  */)
        );
    }
}

final class ExampleSagaId extends SagaId
{

}
```

```
$sagaProvider = new SagaProvider(
    new SQLSagaStore(
        StorageAdapterFactory::inMemory()
    )
);

$promise = $sagaProvider->start(
    ExampleSagaId::new(ExampleSaga::class),
    new SomeStartCommand(),
    new KernelContext()
);
```

On saga creation each saga will be started and saved. It is not necessary to save a saga if there were no changes to its state after start. Saga should be saved if it was obtained from database.

*Saga uniqueness is ensured by a composite key which consists of an UUID and identifier class namespace. Identifier class namespace should be used here to allow launching different sagas with the same identifier (usually all of these are parts of a single business process).*
