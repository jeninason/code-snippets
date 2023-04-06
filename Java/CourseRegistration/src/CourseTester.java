public class CourseTester {
	
	private static boolean passAddTests = true, passDropTests = true;
/*
	public static void main(String[] args) {
		
		// Student objects to use for testing
		Student aa = new Student("Adam Ant", "S925", true);
		Student bb = new Student("Bob Barker", "S713", false);
		Student cc = new Student("Chevy Chase", "S512", true);
		Student dd = new Student("Doris Day", "S513", true);
		Student ee = new Student("Emilio Estevez", "S516", true);
		Student ff = new Student("Farrah Fawcet", "S956", true);
		Student gg = new Student("Greta Garbo", "S419", true);
		Student hh = new Student("Helen Hunt", "S281", true);
		Student jj = new Student("Jack Johnson", "S790", true);
		Student kk = new Student("Kim Kardashian", "S336", true);
		Student mm = new Student("Martina McBride", "S156", true);
		Student rr = new Student("Renne Russo", "S219", true);
		Student ss = new Student("Susan Serandon", "S472", true);
		Student vv = new Student("Vince Vaughn", "S892", true);
		Student ww = new Student("Walt Whitman", "S901", true);

		System.out.println("\n\n------------------------------TESTING ADD METHOD------------------------------");
		// Course object to use for testing add method
		Course addTesterCourse = new Course("Media Studies", 5, 5);
		
		// parameter 1: the course
		// parameter 2: the expected students to display on the roster
		// parameter 3: the expected students to display on the waitlist
		testCourseDisplay(addTesterCourse, new Student[] {}, new Student[] {});

		// parameter 1: the student to add
		// parameter 2: the course
		// parameter 3: whether the student should be added to the roster
		// parameter 4: whether the student should be added to the waitlist
		// parameter 5: test description
		testAddMethod(aa, addTesterCourse, true,  false, "tuition paid and room in course");
		testAddMethod(bb, addTesterCourse, false, false, "tuition not paid");
		testAddMethod(cc, addTesterCourse, true,  false, "tuition paid and room in course");
		testAddMethod(dd, addTesterCourse, true,  false, "tuition paid and room in course");
		testAddMethod(ee, addTesterCourse, true,  false, "tuition paid and room in course");
		testAddMethod(ff, addTesterCourse, true,  false, "tuition paid and room in course");
		testAddMethod(gg, addTesterCourse, false, true,  "tuition paid and room in course");
		testAddMethod(hh, addTesterCourse, false, true,  "tuition paid and room in course");
		testAddMethod(jj, addTesterCourse, false, true,  "tuition paid and room in course");
		testAddMethod(kk, addTesterCourse, false, true,  "tuition paid and room in course");
		testAddMethod(mm, addTesterCourse, false, true,  "tuition paid and room in course");
		testAddMethod(rr, addTesterCourse, false, false, "roster and waitlist full");
		testAddMethod(ss, addTesterCourse, false, false, "roster and waitlist full");
		testAddMethod(vv, addTesterCourse, false, false, "roster and waitlist full");
		testAddMethod(ww, addTesterCourse, false, false, "roster and waitlist full");
		
		testCourseDisplay(addTesterCourse, new Student[] {aa, cc, dd, ee, ff}, new Student[] {gg, hh, jj, kk, mm});

		testAddMethod(cc, addTesterCourse, false, false, "student already enrolled on the roster");
		testAddMethod(hh, addTesterCourse, false, false, "student already enrolled on the waitlist");

		
		System.out.println("\n\n------------------------------TESTING DROP METHOD------------------------------");
		// creates a new course in case you want to comment out the add tests 
		// once you are ready to focus on the drop method
		Course dropTesterCourse = new Course("Media Studies", 5, 5);
		dropTesterCourse.addStudent(aa); dropTesterCourse.addStudent(cc); dropTesterCourse.addStudent(dd); dropTesterCourse.addStudent(ee);
		dropTesterCourse.addStudent(ff); dropTesterCourse.addStudent(gg); dropTesterCourse.addStudent(hh);
		dropTesterCourse.addStudent(jj); dropTesterCourse.addStudent(kk); dropTesterCourse.addStudent(mm);
		
		testCourseDisplay(dropTesterCourse, new Student[] {aa, cc, dd, ee, ff}, new Student[] {gg, hh, jj, kk, mm});
		
		// parameter 1: the student to drop
		// parameter 2: the course
		// parameter 3: whether the student should be dropped from the roster
		// parameter 4: whether the student should be dropped from the waitlist
		// parameter 5: test description
		testDropMethod(cc, dropTesterCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(dropTesterCourse, new Student[] {aa, dd, ee, ff, gg}, new Student[] {hh, jj, kk, mm});

		testDropMethod(ww, dropTesterCourse, false, false, "student is not enrolled");
		testCourseDisplay(dropTesterCourse, new Student[] {aa, dd, ee, ff, gg}, new Student[] {hh, jj, kk, mm});

		testDropMethod(jj, dropTesterCourse, false, true, "student enrolled on the waitlist");
		testCourseDisplay(dropTesterCourse, new Student[] {aa, dd, ee, ff, gg}, new Student[] {hh, kk, mm});

		testDropMethod(aa, dropTesterCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(dropTesterCourse, new Student[] {dd, ee, ff, gg, hh}, new Student[] {kk, mm});
		
		testDropMethod(dd, dropTesterCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(dropTesterCourse, new Student[] {ee, ff, gg, hh, kk}, new Student[] {mm});
		
		testDropMethod(dd, dropTesterCourse, false, false, "student is not enrolled");
		testCourseDisplay(dropTesterCourse, new Student[] {ee, ff, gg, hh, kk}, new Student[] {mm});

		testDropMethod(ee, dropTesterCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(dropTesterCourse, new Student[] {ff, gg, hh, kk, mm}, new Student[] {});

		testDropMethod(ff, dropTesterCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(dropTesterCourse, new Student[] {gg, hh, kk, mm}, new Student[] {});

		testDropMethod(gg, dropTesterCourse, true, false, "student enrolled on the roster");
		testDropMethod(hh, dropTesterCourse, true, false, "student enrolled on the roster");
		testDropMethod(kk, dropTesterCourse, true, false, "student enrolled on the roster");
		testDropMethod(mm, dropTesterCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(dropTesterCourse, new Student[] {}, new Student[] {});
		
		testDropMethod(aa, dropTesterCourse, false, false, "student is not enrolled");
		testCourseDisplay(dropTesterCourse, new Student[] {}, new Student[] {});

		System.out.println("\n\n------------------------------TESTING A MIX OF ADD AND DROP------------------------------");
		// I've created another course in case you want to add any additional testing
		Course extraTesterCourse = new Course("Media Studies", 5, 5);
		extraTesterCourse.addStudent(aa); extraTesterCourse.addStudent(cc); extraTesterCourse.addStudent(dd); extraTesterCourse.addStudent(ee);
		extraTesterCourse.addStudent(ff); extraTesterCourse.addStudent(gg); extraTesterCourse.addStudent(hh);
		extraTesterCourse.addStudent(jj); extraTesterCourse.addStudent(kk); extraTesterCourse.addStudent(mm);
	
		Course smallCourse = new Course("Independent Study", 2, 2);
		testAddMethod(aa, smallCourse, true, false, "tuition paid and room in course");
		testAddMethod(cc, smallCourse, true, false, "tuition paid and room in course");
		testAddMethod(dd, smallCourse, false, true, "tuition paid and room in course");
		testAddMethod(ee, smallCourse, false, true, "tuition paid and room in course");
		testAddMethod(ff, smallCourse, false, false, "roster and waitlist full");
		testDropMethod(cc, smallCourse, true, false, "student enrolled on the roster");
		testCourseDisplay(smallCourse, new Student[] {aa, dd}, new Student[] {ee});
		testDropMethod(ee, smallCourse, false, true, "student enrolled on the waitlist");
		testAddMethod(ff, smallCourse, false, true, "tuition paid and room in course");
		testAddMethod(gg, smallCourse, false, true, "tuition paid and room in course");
		testCourseDisplay(smallCourse, new Student[] {aa, dd}, new Student[] {ff, gg});
		
		
		if(passAddTests && passDropTests) {
			System.out.println("\n------------------------------ Summary ------------------------------ \nAll automated tests have passed. \nManually review your code for style. \nManually view the expected output to make sure it matches.");
		} else {
			System.out.flush();
			System.err.println("\n**********Summary********** ERROR: There is failure in at least one automated test. Review the output above for details.");
		}

	}
*/
	/*----------------------------------------------------------------------------------------------------------*/
	/* TESTER METHODS */
	/*----------------------------------------------------------------------------------------------------------*/
	/*
	 * The methods below are designed to help support the tests cases run from main. You don't
	 * need to use, modify, or understand these methods. You can safely ignore them. :) 
	 */
	/*
	public static void testAddMethod(Student student, Course course, boolean expectedRosterResult, boolean expectedWaitlistResult, String testDescription) {
		student = new Student(student.getName(), student.getID(), student.isTuitionPaid());
		boolean expectedResult = expectedRosterResult || expectedWaitlistResult;
		int beforeRosterSize = course.getNumEnrolled();
		int beforeWaitlistSize = course.getNumWaitlist();

		System.out.println("\nTrying to add " + student.getName() + " (" + testDescription + ")" + "\tCourse before add: roster size=" + beforeRosterSize + "  waitlist size=" + beforeWaitlistSize);
		boolean actualResult = course.addStudent(student);
		int afterRosterSize = course.getNumEnrolled();
		int afterWaitlistSize = course.getNumWaitlist();	
		
		System.out.println("Expected return result = " + expectedResult);
		System.out.println("  Actual return result = " + actualResult);
		
		if(expectedResult) { // student should be added to either the roster or the waitlist
			if(actualResult!=true) {
				System.out.println("\"**********TEST FAILED: incorrect return result.");
				passAddTests = false;
			}
			if(expectedRosterResult) {
				System.out.println("  Expected after roster size = " + (beforeRosterSize+1));
				System.out.println("    Actual after roster size = " + afterRosterSize);
				System.out.println("Expected after waitlist size = " + beforeWaitlistSize);
				System.out.println("  Actual after waitlist size = " + afterWaitlistSize);
				
				if((beforeRosterSize+1)!=afterRosterSize) {
					System.out.println("\"**********TEST FAILED: roster size should increase by one.");
					passAddTests = false;
				}
				if(beforeWaitlistSize!=afterWaitlistSize) {
					System.out.println("\"**********TEST FAILED: waitlist size should not increase.");
					passAddTests = false;
				}
			} else { // expectedWaitlistResult
				if(expectedWaitlistResult) {
					System.out.println("  Expected after roster size = " + beforeRosterSize);
					System.out.println("    Actual after roster size = " + afterRosterSize);
					System.out.println("Expected after waitlist size = " + (beforeWaitlistSize+1));
					System.out.println("  Actual after waitlist size = " + afterWaitlistSize);
					
					if(beforeRosterSize!=afterRosterSize) {
						System.out.println("\"**********TEST FAILED: roster size should not increase.");
						passAddTests = false;
					}
					if((beforeWaitlistSize+1)!=afterWaitlistSize) {
						System.out.println("\"**********TEST FAILED: waitlist size should increase by one.");
						passAddTests = false;
					}
				}
			}
			
		} else { // student should not be added to either: expectedResult==false
			System.out.println("  Expected after roster size = " + beforeRosterSize);
			System.out.println("    Actual after roster size = " + afterRosterSize);
			System.out.println("Expected after waitlist size = " + beforeWaitlistSize);
			System.out.println("  Actual after waitlist size = " + afterWaitlistSize);
			if(actualResult!=false) {
				System.out.println("\"**********TEST FAILED: incorrect return result.");
				passAddTests = false;
			}
			if(beforeRosterSize!=afterRosterSize) {
				System.out.println("\"**********TEST FAILED: roster size should not increase.");
				passAddTests = false;
			}
			if(beforeWaitlistSize!=afterWaitlistSize) {
				System.out.println("\"**********TEST FAILED: waitlist size should not increase.");
				passAddTests = false;
			}
			
		}
	}
	public static void testDropMethod(Student student, Course course, boolean expectedRosterResult, boolean expectedWaitlistResult, String testDescription) {
		student = new Student(student.getName(), student.getID(), student.isTuitionPaid());
		boolean expectedResult = expectedRosterResult || expectedWaitlistResult;
		int beforeRosterSize = course.getNumEnrolled();
		int beforeWaitlistSize = course.getNumWaitlist();

		System.out.println("\nTrying to drop " + student.getName() + " (" + testDescription + ")" + "\tCourse before drop: roster size=" + beforeRosterSize + "  waitlist size=" + beforeWaitlistSize);
		boolean actualResult = course.dropStudent(student);
		int afterRosterSize = course.getNumEnrolled();
		int afterWaitlistSize = course.getNumWaitlist();	
		
		System.out.println("Expected return result = " + expectedResult);
		System.out.println("  Actual return result = " + actualResult);
		
		if(expectedResult) { // student should be dropped from either the roster or the waitlist
			if(actualResult!=true) {
				System.out.println("\"**********TEST FAILED: incorrect return result.");
				passDropTests = false;
			}
			if(expectedRosterResult) {
				int expectedAfterRosterSize, expectedAfterWaitlistSize;
				if(beforeWaitlistSize>0) {
					expectedAfterRosterSize = beforeRosterSize;
					expectedAfterWaitlistSize = beforeWaitlistSize - 1;
				} else {
					expectedAfterRosterSize = beforeRosterSize - 1;
					expectedAfterWaitlistSize = beforeWaitlistSize;
				}
				System.out.println("  Expected after roster size = " + expectedAfterRosterSize);
				System.out.println("    Actual after roster size = " + afterRosterSize);
				System.out.println("Expected after waitlist size = " + expectedAfterWaitlistSize);
				System.out.println("  Actual after waitlist size = " + afterWaitlistSize);
				
				if(expectedAfterRosterSize!=afterRosterSize) {
					System.out.println("\"**********TEST FAILED: incorrect roster size.");
					passDropTests = false;
				}
				if(expectedAfterWaitlistSize!=afterWaitlistSize) {
					System.out.println("\"**********TEST FAILED: incorrect waitlist size.");
					passDropTests = false;
				}
			} else { // expectedWaitlistResult
				if(expectedWaitlistResult) {
					System.out.println("  Expected after roster size = " + beforeRosterSize);
					System.out.println("    Actual after roster size = " + afterRosterSize);
					System.out.println("Expected after waitlist size = " + (beforeWaitlistSize-1));
					System.out.println("  Actual after waitlist size = " + afterWaitlistSize);
					
					if(beforeRosterSize!=afterRosterSize) {
						System.out.println("\"**********TEST FAILED: roster size should not decrease.");
						passDropTests = false;
					}
					if((beforeWaitlistSize-1)!=afterWaitlistSize) {
						System.out.println("\"**********TEST FAILED: waitlist size should decrease by one.");
						passDropTests = false;
					}
				}
			}
			
		} else { // student should not be added to either: expectedResult==false
			System.out.println("  Expected after roster size = " + beforeRosterSize);
			System.out.println("    Actual after roster size = " + afterRosterSize);
			System.out.println("Expected after waitlist size = " + beforeWaitlistSize);
			System.out.println("  Actual after waitlist size = " + afterWaitlistSize);
			if(actualResult!=false) {
				System.out.println("\"**********TEST FAILED: incorrect return result.");
				passDropTests = false;
			}
			if(beforeRosterSize!=afterRosterSize) {
				System.out.println("\"**********TEST FAILED: roster size should not decrease.");
				passDropTests = false;
			}
			if(beforeWaitlistSize!=afterWaitlistSize) {
				System.out.println("\"**********TEST FAILED: waitlist size should not decrease.");
				passDropTests = false;
			}
			
		}
	}
	public static void testCourseDisplay(Course course, Student[] studentsOnRoster, Student[] studentsOnWaitlist) {
		System.out.println("\n----------Invoking toString method: there is no automated test here. Compare the results manually.");
		String rosterString = "", waitlistString = "";
		for(Student student : studentsOnRoster) {
			rosterString += student.getName().substring(0,1) + student.getName().substring(0,1) + " ";
		}
		for(Student student : studentsOnWaitlist) {
			waitlistString += student.getName().substring(0,1) + student.getName().substring(0,1) + " ";
		}
		if(rosterString.length()==0) {
			rosterString = "<none>";
		}
		if(waitlistString.length()==0) {
			waitlistString = "<none>";
		}
		System.out.println("Expected students on roster (in any order):\t\t" + rosterString);
		System.out.println("Expected students on waitlist (in this exact order):\t" + waitlistString);
		System.out.println("Displaying course now...");
		System.out.println(course);
		
	}
	*/
}
