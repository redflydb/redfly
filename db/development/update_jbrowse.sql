UPDATE Species
SET public_browser_names = 'JBrowse,UCSC',
    public_browser_links = 'http://128.205.11.6/jbrowse?coordinate=chromosome:start..end,http://genome.ucsc.edu/cgi-bin/hgTracks?db=release_version&position=chrchromosome:start-end'
WHERE short_name = 'dmel';