<?php
interface iExportFile
{
    public function getHeader();
    public function getBody();
    public function getFooter();
    public function getFile();
    public function getHtmlHeaders(
        $entityName,
        $selectedEntities
    );
}
