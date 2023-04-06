
import java.util.Scanner;
import java.time.LocalDate;

public class CreditCardValidator {

	public static final int CREDIT_CARD_LENGTH = 16;	
	public static final int CURRENT_YEAR = 2022;
	
	public static void main(String[] args) {
		long creditCardNumber = readUserCreditCardNumber();		
		int year = readUserYear();
		int month = readUserMonth(year);

		String outputNum = String.valueOf(creditCardNumber);
		outputNum = outputNum.replaceAll("....", "$0 ");
		
		System.out.println( "Your valid credit card number is " + outputNum + " exp. "+ month + "/" + year);
		
	}
	public static int readUserYear() {
		boolean validUserYear = false;
		int expirationYear = 0;
		while (!validUserYear) {
			System.out.println("Enter the expiration year: ");
			@SuppressWarnings("resource")
			Scanner scan = new Scanner(System.in);
			expirationYear = Integer.parseInt(scan.nextLine());
			if (expirationYear >= CURRENT_YEAR) {
				validUserYear = true;
			} else {
				System.out.println("Your expiration year is NOT valid. Please try again.");
			}
		}
		return expirationYear;
	}

	public static int readUserMonth(int userYear) {
		boolean validUserMonth = false;
		int expirationMonth = 0;
		LocalDate currentDate = LocalDate.now();
		while (!validUserMonth) {
			boolean validCurrentYearMonth = true;
			System.out.println("Enter the expiration month: ");
			Scanner scan = new Scanner(System.in);
			expirationMonth = Integer.parseInt(scan.nextLine());
			if (userYear == currentDate.getYear()) {
				if (expirationMonth < currentDate.getMonthValue() && expirationMonth > 0) {
					validCurrentYearMonth = false;
				} else if (expirationMonth == currentDate.getMonthValue()) {
					System.out.println("Caution: your card expires this month!");
				}
			}
			if (expirationMonth >= 1 
					&& expirationMonth <= 12 
					&& validCurrentYearMonth) { 
				validUserMonth = true;
			} else {
				System.out.println("Your expiration month is NOT valid. Please try again.");
			}
		}
		return expirationMonth;
	}
	
	public static long readUserCreditCardNumber() {
		boolean validCreditCardNumber = false;
		String creditCardNumber = "";
		
		while (!validCreditCardNumber) {
			System.out.println("Enter your 16 digit card number. ");
			Scanner scan = new Scanner(System.in);
			creditCardNumber = scan.nextLine();

			boolean correctLength = creditCardNumber.length() == CREDIT_CARD_LENGTH;

			boolean correctFirstDigit = false;
			if (creditCardNumber.length() > 0) {
				switch (creditCardNumber.charAt(0)) {
				case '4':
				case '5':
				case '6':
					correctFirstDigit = true;
					break;
				default:
					correctFirstDigit = false;
					break;
				}
			}
			validCreditCardNumber = correctLength && correctFirstDigit && passesCheckSum(creditCardNumber);

			if (validCreditCardNumber) {
				validCreditCardNumber = true;
			} else {
				System.out.println("Your credit card number is invalid.");
				if (!correctLength) {
					System.out.println("Your credit card number is not the correct length.");
				}
				if (!correctFirstDigit) {
					System.out.println("Your credit card number does not start with a valid digit.");
				}
				if (!passesCheckSum(creditCardNumber)) {
					System.out.println("Your credit card number fails the check sum method.");
				}
			}
			
		}

		return Long.valueOf(creditCardNumber); 
	}
	
	// this method assumes cardNumber has 16 digits
	public static boolean passesCheckSum(String cardNumber) {
		/*
		 * 1. Multiply every other digit by 2, if result more than 1 digit, add together
		 * 2. Add new 15 digits with the 16th digit
		 * 3. Check if divisible by 10
		 */
		int sum = 0;
		for (int i = 0; i < cardNumber.length() -1; i++) {
			if (i % 2 > 0) { //odd number, just add to sum
				sum += convertDigitCharToInt(cardNumber.charAt(i));
			} else {
				int digitToMultiply = convertDigitCharToInt(cardNumber.charAt(i));
				int doubledDigit = digitToMultiply * 2;
				if (doubledDigit > 9) {
					int addTwoDigits = doubledDigit % 10 + doubledDigit / 10; //(add the remainder over 10 + the tens)
					sum += addTwoDigits;
				} else {
					sum += doubledDigit;
				}
			}
		}
		/*
		 *  convert 16th digit of cardNumber to int
		 *  add sum + 16th
		 */
		sum += convertDigitCharToInt(cardNumber.charAt(cardNumber.length()-1));
		/* 
		 * if result % 10 == 0, then pass
		 */
		if (sum % 10 == 0) {
			return true;
		} else {
			return false; 
		}
	}


	/*
	 * Below is a collection of helper methods you might or might not use.
	 * Don't worry too much about understanding these- we'll learn more
	 * about them later on. For now, you can just use them!
	 */
	
	// example: pass in the char '3' and return the int 3
	public static int convertDigitCharToInt(char digit) {
		return Integer.valueOf(String.valueOf(digit));
	}
	
	// example: pass in the String "4" and return the int 4
	public static int convertDigitStringToInt(String digit) {
		return Integer.valueOf(digit);
	}
	
	// example: pass in the int 5 and return the String "5"
	public static String convertIntToString(int number) {
		return Integer.toString(number);
	}
	
	// this method assumes the int parameter is a single digit; it will not work properly otherwise
	// example: pass in the int 6 and return the char '6'
	public static char convertSingleDigitIntToChar(int digit) {
		return convertIntToString(digit).charAt(0);
	}

}
