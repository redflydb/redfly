# Coordinates Duplicate Conditions Applied On Transcription Factor Binding Sites

Let the coordinates of an entity belonging to the transcription factor binding site kind, "t", be defined as (start, end) with the following definitions:

1) start and end belong to the set of positive integer numbers
2) 0 < start < infinity
3) 0 < end < infinity
4) start < end

Let's suppose that the coordinates of "t" are already in the database and we are going to insert the coordinates of another new entity, "t_d", or edit the coordinates of another existing entity, "t_d".

The necessary conditions of an entity duplicate, "r_d", which coordinates are (start_d, end_d), are defined as:

1) both entities, "t" and "t_d", belong to the same entity kind
2) the state of the last version of "r" is not set as "archived"
3) the chromosomes of both entities, "t" and "t_d", are the same one
4) start = start_d
5) end = end_d
