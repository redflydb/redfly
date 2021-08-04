# Enhancer And Silencer Attribute Conditions Applied On Reporter Constructs

Let be A and B two sets.  
The first set, A, contains entities not having any anatomical expression, or having an anatomical expression without any staging data, at least, or an anatomical expression having the enhancer attribute, at least.  
The second set, B, contains entities having an anatomical expression having the silencer attribute, at least.  

1 ) Include enhancer: A  
Automatically filtered by the condition "is negative" = FALSE

2 ) Include silencer: B  
Automatically filtered by the condition "is negative" = FALSE

3 ) Exclude enhancer: B - A  
Automatically filtered by the condition "is negative" = FALSE

4 ) Exclude silencer: A - B  
Automatically filtered by the condition "is negative" = FALSE

1 and 2 ) Has both enhancer and silencer attributes included: A ∩ B  
Automatically filtered by the condition "is negative" = FALSE

1 and 3 ) Disallowed as mutually exclusive, that is, not allowed in the user interface  
Not applicable here

1 and 4 ) Has enhancer(s) only: A - (A ∩ B)  
Automatically filtered by the condition "is negative" = FALSE

2 and 3 ) Has silencer(s) only: B - (A ∩ B)  
Automatically filtered by the condition "is negative" = FALSE

2 and 4 ) Disallowed as mutually exclusive, that is, not allowed in the user interface  
Not applicable here

3 and 4 ) Has both enhancer and silencer attributes excluded: NOT (A U B)  
Automatically filtered by the condition "is negative" = TRUE