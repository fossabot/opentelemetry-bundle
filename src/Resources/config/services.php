<?php

use GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\EventSubscriber\HttpKernelEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetryBundle;
use GaelReyrol\OpenTelemetryBundle\Propagator\HeadersPropagator;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\Tracer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('open_telemetry.bundle.name', OpenTelemetryBundle::name())
        ->set('open_telemetry.bundle.version', OpenTelemetryBundle::version())
    ;

    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.text_map_propagators.noop', NoopTextMapPropagator::class)
        ->set('open_telemetry.propagation_getters.headers', HeadersPropagator::class)

        ->set('open_telemetry.instrumentation.http_kernel.event_subscriber', HttpKernelEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$propagator', service('open_telemetry.text_map_propagators.noop'))
            ->arg('$propagationGetter', service('open_telemetry.propagation_getters.headers'))

            ->arg('$requestHeaders', param('open_telemetry.instrumentation.http_kernel.request_headers'))
            ->arg('$responseHeaders', param('open_telemetry.instrumentation.http_kernel.response_headers'))

        ->set('open_telemetry.instrumentation.console.event_subscriber', ConsoleEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))

        ->set('open_telemetry.traces.samplers.always_on', AlwaysOnSampler::class)
        ->set('open_telemetry.traces.samplers.always_off', AlwaysOffSampler::class)
        ->set('open_telemetry.traces.samplers.trace_id_ratio_based', TraceIdRatioBasedSampler::class)
        ->set('open_telemetry.traces.samplers.parent_based', ParentBased::class)

        ->set('open_telemetry.traces.exporter' /* , SpanExporterInterface::class */)
            ->synthetic()
        ->set('open_telemetry.traces.processor' /* , SpanProcessorInterface::class */)
            ->synthetic()
        ->set('open_telemetry.traces.provider' /* , TracerProviderInterface::class */)
            ->synthetic()
        ->set('open_telemetry.traces.tracer', Tracer::class)
            ->synthetic()
    ;
};
