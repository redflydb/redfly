The iCRM calculation utility
----------------------------

This utility calculates the iCRMs and stores them in the database.

- To use, edit `main.go` to update the `dsn` constant with the appropriate database credentials (see example in the comments therein), then compile and run.

The high-level pseudocode for the algorithm is as follows:

```
1. let iCRMs be an empty collection;
2. overlaps <- calculate all possible overlaps between two CRMs, grouped by a
               common CRM;
3. for each overlap in overlaps:
4.     sets <- calculate the power set of each group of overlaps;
5.     for each set in sets:
6.         if set is a valid iCRM:
7.             iCRMs <- build iCRM from set;
8. return iCRMs;
```

An overlap is when the coordinates of two CRMs overlap and the intersection of their anatomical expressions is not empty. A simple example could look like (E1, E2, etc represent expressions):

```
1.    |=====|    E1, E2,     E4,
2.       |=====|     E2, E3
3. |=====|               E3, E4
```

The first CRM overlaps the second and third; the second and third do not overlap each other.

In the above pseudocode, overlaps grouped by CRM means all overlaps that involves a single common CRM. The above simple example could be an instance of a group since all overlaps involve the first CRM.

A valid iCRM is defined as the overlap between two or more CRMs and the intersection of their anatomical expressions, all of which must be unique.

The iCRM must:
- Not occupy the same exact sequence as another iCRM; in this case, a new iCRM will be created with the union of the two iCRMs' components and the intersection of the two iCRMs' expression terms, and this iCRM will replace the existing iCRM in this collection, and
- Not enclose any other iCRMs in the collection; in this case, the iCRM will simply be disregarded, and
- Not be enclosed by any other iCRM in the collection; in this case the enclosing iCRM will be removed from the collection and the new iCRM will be added.
