<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
        <parameter key="sensio_framework_extra.routing.loader.annot_dir.class">Symfony\Component\Routing\Loader\AnnotationDirectoryLoader</parameter>
        <parameter key="sensio_framework_extra.routing.loader.annot_file.class">Symfony\Component\Routing\Loader\AnnotationFileLoader</parameter>
        <parameter key="sensio_framework_extra.routing.loader.annot_class.class">Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader</parameter>
    </parameters>

    <services>
        <service id="sensio_framework_extra.routing.loader.annot_dir" class="%sensio_framework_extra.routing.loader.annot_dir.class%" public="false">
            <tag name="routing.loader" />
            <argument type="service" id="file_locator" />
            <argument type="service" id="sensio_framework_extra.routing.loader.annot_class" />
        </service>

        <service id="sensio_framework_extra.routing.loader.annot_file" class="%sensio_framework_extra.routing.loader.annot_file.class%" public="false">
            <tag name="routing.loader" />
            <argument type="service" id="file_locator" />
            <argument type="service" id="sensio_framework_extra.routing.loader.annot_class" />
        </service>

        <service id="sensio_framework_extra.routing.loader.annot_class" class="%sensio_framework_extra.routing.loader.annot_class.class%" public="false">
            <tag name="routing.loader" />
            <argument type="service" id="annotation_reader" />
        </service>
    </services>
</container>
