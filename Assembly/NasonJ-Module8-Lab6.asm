# Jennifer Nason CS270 Module 8 Lab 6
# Write a program that uses two loops to find the integer values that are in both arrays.  
# When an integer value is found to be in both arrays, place the integer value in a $t register.  
# The $t registers should have the integer values placed in them in this way:
# - the first integer value found to be in both arrays should be placed in the register $t0
# - the second integer value found to be in both arrays should be placed in the register $t1 etc

.data # data segment     
array1:     .word 29, 106, 18, 2, 55, 21, 17, 13, 9999, 1024, 13, 2, 5, 23, 51, 2021, 111, 89, 89, 91, 861, 1234, 5004
array2:     .word 91, 15, 767, 861, 89, 21, 1000, 1234
outerLength:    .word 23
innerLength:    .word 8

.text # code segment

main:
    lw $s0, outerLength # load length into $s0
    addi $s2, $zero, 0 # initialize $s2 to zero to use as counter
    la $a0, array1 # load address of array1 into $a0
    addi $t6, $zero, 0 # init the match counter to figure out which register to store the value in
    lw $s1, innerLength # load length into $s1

# Variable list
# $s0 = outerLength
# $s1 = innerLength
# $s2 = outer counter
# $s3 = inner counter
# $a0 = pointer address of array1
# $a1 = pointer address of array2
# $t7 = value at address $a0 to compare
# $t6 = counter of matches
# $t0 = first matching array value
# $t1 = second matching array value
# $t3 = third matching array value
# $t4 = fourth matching array value

outerLoop:
    beq $s0, $s2, end # if the counter is equal to the length of the outerArray, end
    # iterate the outside loop counter
    addi $s2, $s2, 1
    
    la $a1, array2 # load address of array2 into $a1

    lw $t7, 0($a0) # load value at address $a0 into $t7 outer array value
    addi $a0, $a0, 4 # increment address by 4 for outer array before doing anything else

    addi $s3, $zero, 0 # initialize $s3 to zero to use as counter for inner loop

    innerLoop:
        beq $s3, $s1, outerLoop # if the counter is equal to the length of the innerArray, send back to outerloop
        addi $s3, $s3, 1 # increment $s3 by 1 for inner counter

        lw $t8, 0($a1) # load value at address $a1 into $t8 inner array value
	addi $a1, $a1, 4 # increment address by 4 for inner array before doing anything else

        bne $t7, $t8, innerLoop # if $t7 is NOT equal to $t8, jump to innerLoop again 
        
	#iterate the counter of matches
	addi $t6, $t6, 1 #iterate the match counter
        #since they matched, just store the value in the correct register and jump to outerLoop
        beq $t6, 1, firstMatch # jump to firstMatch
        beq $t6, 2, secondMatch # jump to secondMatch
        beq $t6, 3, thirdMatch # jump to thirdMatch
        beq $t6, 4, fourthMatch # jump to fourthMatch
        beq $t6, 5, fifthMatch # jump to fifthMatch
        beq $t6, 6, sixthMatch # jump to fifthMatch
        
        #this doesn't handle more than 6 matches. I'd prefer to use the stack and a function, or perhaps save to an array and store after
        
        #after the match, jump to the outer loop, since the inner match was already found
        j outerLoop # I think this is redundant, just doesn't break if there's more than 6 matches

#this is kinda a switch statement
firstMatch:
    move $t0, $t7 # load match into $t0
    j outerLoop # jump to loop

secondMatch:
    move $t1, $t7 # load match into $t1
    j outerLoop # jump to loop

thirdMatch:
    move $t2, $t7 # load match into $t2
    j outerLoop # jump to loop

fourthMatch:
    move $t3, $t7 # load match into $t3
    j outerLoop # jump to loop

fifthMatch:
    move $t4, $t7 # load match into $t4
    j outerLoop # jump to loop
sixthMatch:
    move $t5, $t7 # load match into $t5
    j outerLoop # jump to loop
end:

# just for testing
#li $v0, 1           # Load syscall code 1 (print integer)
#    move $a0, $t5       # Move the value from $t0 to $a0
#    syscall



#clean exit
    li $v0, 10 # load 10 into $v0
    syscall # exit
