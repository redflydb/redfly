# Coordinates Duplicate Conditions Applied On Reporter Constructs

Let the coordinates of an entity belonging to the reporter construct kind, "r", be defined as (start, end) with the following definitions:

1) start and end belong to the set of positive integer numbers
2) 0 < start < infinity
3) 0 < end < infinity
4) start < end

Let's suppose that the coordinates of "r" are already in the database and we are going to insert the coordinates of another new entity, "r_d", or edit the coordinates of another existing entity, "r_d".

The necessary conditions of an entity duplicate, "r_d", which coordinates are (start_d, end_d), using an unique error margin are defined as:

1) both entities, "r" and "r_d", belong to the same entity kind
2) the state of the last version of "r" is not set as "archived"
3) the chromosomes of both entities, "r" and "r_d", are the same one
4) error_margin belongs to the set of positive integer numbers
5) 0 < error_margin < infinity
6) start - error_margin <= start_d <= start + error_margin
7) end - error_margin <= end_d <= end + error_margin

The number of potential coordinates duplicates fulfilling the list of such conditions can be calculated by the formula: ((2 * error_margin) + 1) ** 2

For example, let the coordinates (start, end) of a reporter construct, "r", be (20000, 20100) with the error margin defined as 3.\
Its number of potential coordinates duplicates is ((2 * 3) + 1) ** 2 = 7 ** 2 = 49.\
Then the set of all potential reporter construct duplicate coordinates basing on the example given is:

{(19997, 20097), (19997, 20098), (19997, 20099), (19997, 20100), (19997, 20101), (19997, 20102), (19997, 20103),\
 (19998, 20097), (19998, 20098), (19998, 20099), (19998, 20100), (19998, 20101), (19998, 20102), (19998, 20103),\
 (19999, 20097), (19999, 20098), (19999, 20099), (19999, 20100), (19999, 20101), (19999, 20102), (19999, 20103),\
 (20000, 20097), (20000, 20098), (20000, 20099), (20000, 20100), (20000, 20101), (20000, 20102), (20000, 20103),\
 (20001, 20097), (20001, 20098), (20001, 20099), (20001, 20100), (20001, 20101), (20001, 20102), (20001, 20103),\
 (20002, 20097), (20002, 20098), (20002, 20099), (20002, 20100), (20002, 20101), (20002, 20102), (20002, 20103),\
 (20003, 20097), (20003, 20098), (20003, 20099), (20003, 20100), (20003, 20101), (20003, 20102), (20003, 20103)}

All the possible coordinates cases not making any entity duplicate are:

1) the coordinates, (19900, 20000), of a first reporter construct, "r1", overlapping by the left of "r" beyond both error margins do not make "r1" as a duplicate.

2) the coordinates, (20050, 20150), of a second reporter construct, "r2", overlapping by the right of "r" beyond both error margins do not make "r2" as a duplicate.

3) the coordinates, (19998, 20150), of a third reporter construct, "r3", overlapping by the left of "r" beyond the error margin at the right do not make "r3" as a duplicate.

4) the coordinates, (20050, 20102), of a fourth reporter construct, "r4", overlapping by the right of "r" beyond the error margin at the left do not make "r4" as a duplicate.

5) the coordinates, (20030, 20080), of a fifth reporter construct, "r5", enclosed inside of "r" beyond both error margins do not make "r5" as a duplicate.

6) the coordinates, (20002, 20080), of a sixth reporter construct, "r6", enclosed inside of "r" beyond the error margin placed at the right do not make "r6" as a duplicate.

7) the coordinates, (20010, 20098), of a seventh reporter construct, "r7", enclosed inside of "r" beyond the error margin placed at the left do not make "r7" as a duplicate.

8) the coordinates, (18000, 20300), of an eighth reporter construct, "r8", enclosing "r" beyond both error margins do not make "r8" as a duplicate.

9) the coordinates, (19997, 20300), of a ninth reporter construct, "r9", enclosing "r" beyond the error margin placed at the right do not make "r9" as a duplicate.

10) the coordinates, (18000, 20103), of a tenth reporter construct, "r10", enclosing "r" beyond the error margin placed at the left do not make "r10" as a duplicate.
