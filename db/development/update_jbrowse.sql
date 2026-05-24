UPDATE Species
SET public_browser_names = 'JBrowse,UCSC',
    public_browser_links = 'https://redfly.ccr.buffalo.edu/jbrowse?coordinate=chromosome:start..end,http://genome.ucsc.edu/cgi-bin/hgTracks?db=release_version&position=chrchromosome:start-end'
WHERE short_name = 'dmel';